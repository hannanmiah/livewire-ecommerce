<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Create order from cart. Uses DB transaction and row-level locking.
     * Stock is RESERVED (not deducted) until payment is confirmed.
     */
    public function createOrderFromCart(Cart $cart, User $user, array $shippingAddress, ?array $billingAddress = null): Order
    {
        return DB::transaction(function () use ($cart, $user, $shippingAddress, $billingAddress): Order {
            $cart->load('items.variant.stock', 'items.variant.product', 'items.variant.attributeValues.attribute', 'coupon');

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_status' => 'pending',
                'subtotal' => $cart->subtotal,
                'shipping_fee' => 0,
                'discount' => $cart->discount,
                'total' => $cart->total,
                'coupon_id' => $cart->coupon?->id,
            ]);

            foreach ($cart->items as $cartItem) {
                $variant = $cartItem->variant;
                $stock = Stock::lockForUpdate()->where('variant_id', $variant->id)->first();

                if (! $stock || $stock->available_quantity < $cartItem->quantity) {
                    throw new \Exception("Insufficient stock for variant {$variant->sku}");
                }

                $stock->increment('reserved_quantity', $cartItem->quantity);

                $description = $variant->product->name;
                $variantAttributes = $variant->attributeValues->map(fn ($av) => $av->attribute->name.': '.$av->value)->implode(', ');

                OrderItem::create([
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'description' => $description.($variantAttributes ? ' ('.$variantAttributes.')' : ''),
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'total' => $cartItem->total,
                    'variant_attributes' => $variant->attributeValues->mapWithKeys(fn ($av) => [$av->attribute->name => $av->value])->toArray(),
                ]);
            }

            if ($cart->coupon_code && $cart->coupon) {
                $couponService = new CouponService;
                $couponService->recordUsage($cart->coupon, $user, $order);
            }

            $user->addresses()->create(array_merge($shippingAddress, ['type' => 'shipping']));

            if ($billingAddress) {
                $user->addresses()->create(array_merge($billingAddress, ['type' => 'billing']));
            }

            Payment::create([
                'order_id' => $order->id,
                'method' => 'pending',
                'amount' => $order->total,
                'status' => 'pending',
            ]);

            (new CartService)->clearCart($cart);

            return $order;
        });
    }

    /**
     * Create a direct order (Buy Now). No cart involved.
     */
    public function createDirectOrder(User $user, int $variantId, int $quantity, array $shippingAddress, ?array $billingAddress = null): Order
    {
        return DB::transaction(function () use ($user, $variantId, $quantity, $shippingAddress, $billingAddress): Order {
            $variant = ProductVariant::with('product', 'attributeValues.attribute')->findOrFail($variantId);
            $stock = Stock::lockForUpdate()->where('variant_id', $variantId)->first();

            if (! $stock || $stock->available_quantity < $quantity) {
                throw new \Exception('Insufficient stock.');
            }

            $stock->increment('reserved_quantity', $quantity);

            $price = (float) $variant->effective_price;
            $total = $price * $quantity;

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipping_status' => 'pending',
                'subtotal' => $total,
                'shipping_fee' => 0,
                'discount' => 0,
                'total' => $total,
            ]);

            $description = $variant->product->name;
            $variantAttributes = $variant->attributeValues->map(fn ($av) => $av->attribute->name.': '.$av->value)->implode(', ');

            OrderItem::create([
                'order_id' => $order->id,
                'variant_id' => $variant->id,
                'description' => $description.($variantAttributes ? ' ('.$variantAttributes.')' : ''),
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
                'variant_attributes' => $variant->attributeValues->mapWithKeys(fn ($av) => [$av->attribute->name => $av->value])->toArray(),
            ]);

            $user->addresses()->create(array_merge($shippingAddress, ['type' => 'shipping']));

            if ($billingAddress) {
                $user->addresses()->create(array_merge($billingAddress, ['type' => 'billing']));
            }

            Payment::create([
                'order_id' => $order->id,
                'method' => 'pending',
                'amount' => $order->total,
                'status' => 'pending',
            ]);

            return $order;
        });
    }

    /**
     * Simulate payment processing.
     */
    public function processPayment(Order $order, string $method): bool
    {
        return DB::transaction(function () use ($order, $method): bool {
            $payment = Payment::lockForUpdate()->where('order_id', $order->id)->first();

            if (! $payment || $payment->status !== 'pending') {
                return false;
            }

            $payment->update([
                'method' => $method,
                'status' => 'success',
                'transaction_id' => 'TXN-'.strtoupper(Str::random(12)),
                'paid_at' => now(),
            ]);

            $order->update([
                'status' => 'paid',
                'payment_status' => 'paid',
            ]);

            $this->finalizeStock($order);

            return true;
        });
    }

    /**
     * Finalize stock — move reserved to actual deduction after payment success.
     */
    protected function finalizeStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $stock = Stock::lockForUpdate()->where('variant_id', $item->variant_id)->first();

            if ($stock) {
                $stock->decrement('quantity', $item->quantity);
                $stock->decrement('reserved_quantity', $item->quantity);
            }
        }
    }

    /**
     * Cancel order — release reserved stock back.
     */
    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            foreach ($order->items as $item) {
                $stock = Stock::lockForUpdate()->where('variant_id', $item->variant_id)->first();

                if ($stock) {
                    $stock->decrement('reserved_quantity', $item->quantity);
                }
            }

            $order->update([
                'status' => 'cancelled',
                'payment_status' => $order->payment_status === 'paid' ? 'paid' : 'unpaid',
            ]);

            if ($order->payment && $order->payment->status === 'success') {
                $order->payment->update(['status' => 'failed']);
            }
        });
    }
}
