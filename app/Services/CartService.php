<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getOrCreateCart(?User $user = null, ?string $sessionId = null): Cart
    {
        $user = $user ?? Auth::user();
        $sessionId = $sessionId ?? Session::getId();

        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
        } else {
            $cart = Cart::where('session_id', $sessionId)->first();
        }

        if (! $cart) {
            $cart = Cart::create([
                'user_id' => $user?->id,
                'session_id' => $user ? null : $sessionId,
            ]);
        }

        return $cart;
    }

    public function addItem(Cart $cart, int $variantId, int $quantity = 1, ?string $description = null): CartItem
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);

        $existingItem = $cart->items()->where('variant_id', $variantId)->first();

        if ($existingItem) {
            $existingItem->quantity += $quantity;
            $existingItem->price = $variant->effective_price;
            $existingItem->description = $description ?? $existingItem->description;
            $existingItem->save();
            $cart->recalculate();

            return $existingItem;
        }

        $item = $cart->items()->create([
            'variant_id' => $variantId,
            'quantity' => $quantity,
            'price' => $variant->effective_price,
            'description' => $description,
        ]);

        $cart->recalculate();

        return $item;
    }

    public function updateItemQuantity(Cart $cart, int $itemId, int $quantity): void
    {
        $item = $cart->items()->findOrFail($itemId);

        if ($quantity <= 0) {
            $this->removeItem($cart, $itemId);

            return;
        }

        $item->update(['quantity' => $quantity]);
        $cart->recalculate();
    }

    public function removeItem(Cart $cart, int $itemId): void
    {
        $cart->items()->findOrFail($itemId)->delete();
        $cart->recalculate();
    }

    public function applyCoupon(Cart $cart, string $code): bool
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->isValid()) {
            return false;
        }

        if ($coupon->min_order_amount && (float) $cart->subtotal < (float) $coupon->min_order_amount) {
            return false;
        }

        $cart->update(['coupon_code' => $coupon->code]);
        $cart->recalculate();

        return true;
    }

    public function removeCoupon(Cart $cart): void
    {
        $cart->update(['coupon_code' => null]);
        $cart->recalculate();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update(['coupon_code' => null]);
        $cart->recalculate();
    }

    public function getCartItemCount(?User $user = null, ?string $sessionId = null): int
    {
        $cart = $this->getCartForContext($user, $sessionId);

        return $cart ? $cart->items->count() : 0;
    }

    public function getCartForContext(?User $user = null, ?string $sessionId = null): ?Cart
    {
        $user = $user ?? Auth::user();
        $sessionId = $sessionId ?? Session::getId();

        if ($user) {
            return Cart::with('items.variant.product')->where('user_id', $user->id)->first();
        }

        return Cart::with('items.variant.product')->where('session_id', $sessionId)->first();
    }

    public function mergeSessionCartToUser(User $user, string $sessionId): void
    {
        $sessionCart = Cart::where('session_id', $sessionId)->first();

        if (! $sessionCart) {
            return;
        }

        $userCart = $this->getOrCreateCart($user);

        foreach ($sessionCart->items as $item) {
            $existing = $userCart->items()->where('variant_id', $item->variant_id)->first();

            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $item->quantity]);
            } else {
                $item->update(['cart_id' => $userCart->id]);
            }
        }

        $sessionCart->delete();
        $userCart->recalculate();
    }
}
