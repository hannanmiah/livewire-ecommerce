<?php

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;

test('checkout requires authentication', function () {
    $this->get(route('checkout'))->assertRedirect(route('login'));
});

test('checkout redirects to cart when empty', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('checkout'))
        ->assertRedirect(route('cart'));
});

test('checkout page loads with cart items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 50.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    $this->actingAs($user)
        ->get(route('checkout'))
        ->assertOk()
        ->assertSee('Checkout');
});

test('user can place order from cart via service', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => 50.00, 'product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id, 'quantity' => 100]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 2);

    $orderService = new OrderService;
    $order = $orderService->createOrderFromCart($cart->fresh(), $user, [
        'address_line' => '123 Test St',
        'city' => 'Test City',
        'postal_code' => '12345',
        'country' => 'Test Country',
    ]);

    expect($order)->not->toBeNull();
    expect($order->status)->toBe('pending');
    expect($order->payment_status)->toBe('unpaid');
    expect((float) $order->subtotal)->toEqual(100.00);
    expect($order->items)->toHaveCount(1);

    // Verify stock was reserved (not deducted)
    $stock = $variant->stock->fresh();
    expect($stock->quantity)->toBe(100);
    expect($stock->reserved_quantity)->toBe(2);

    // Verify payment was created
    expect($order->payment)->not->toBeNull();
    expect($order->payment->status)->toBe('pending');

    // Verify shipping address was saved
    expect($user->addresses()->where('type', 'shipping')->count())->toBe(1);
});

test('user can place order with billing address', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => 50.00, 'product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id, 'quantity' => 100]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    $orderService = new OrderService;
    $order = $orderService->createOrderFromCart($cart->fresh(), $user, [
        'address_line' => '123 Shipping St',
        'city' => 'Ship City',
        'postal_code' => '12345',
        'country' => 'Ship Country',
    ], [
        'address_line' => '456 Billing Ave',
        'city' => 'Bill City',
        'postal_code' => '67890',
        'country' => 'Bill Country',
    ]);

    expect($user->addresses()->where('type', 'shipping')->count())->toBe(1);
    expect($user->addresses()->where('type', 'billing')->count())->toBe(1);
});

test('payment simulation succeeds', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => 50.00, 'product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id, 'quantity' => 100]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    $orderService = new OrderService;
    $order = $orderService->createOrderFromCart($cart->fresh(), $user, [
        'address_line' => '123 Test St',
        'city' => 'City',
        'postal_code' => '123',
        'country' => 'Country',
    ]);

    $result = $orderService->processPayment($order, 'card');

    expect($result)->toBeTrue();
    expect($order->fresh()->status)->toBe('paid');
    expect($order->fresh()->payment_status)->toBe('paid');

    // Verify stock finalized
    $stock = $variant->stock->fresh();
    expect($stock->quantity)->toBe(99);
    expect($stock->reserved_quantity)->toBe(0);

    // Verify payment updated
    $payment = $order->fresh()->payment;
    expect($payment->method)->toBe('card');
    expect($payment->status)->toBe('success');
    expect($payment->transaction_id)->not->toBeNull();
    expect($payment->paid_at)->not->toBeNull();
});

test('payment fails for already paid order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => 50.00, 'product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id, 'quantity' => 100]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    $orderService = new OrderService;
    $order = $orderService->createOrderFromCart($cart->fresh(), $user, [
        'address_line' => '123 Test St',
        'city' => 'City',
        'postal_code' => '123',
        'country' => 'Country',
    ]);

    // Pay first time
    $orderService->processPayment($order, 'card');

    // Try paying again
    $result = $orderService->processPayment($order->fresh(), 'card');

    expect($result)->toBeFalse();
});

test('order cancellation releases reserved stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => 50.00, 'product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id, 'quantity' => 100]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 3);

    $orderService = new OrderService;
    $order = $orderService->createOrderFromCart($cart->fresh(), $user, [
        'address_line' => '123 Test St',
        'city' => 'City',
        'postal_code' => '123',
        'country' => 'Country',
    ]);

    $orderService->cancelOrder($order);

    expect($order->fresh()->status)->toBe('cancelled');

    $stock = $variant->stock->fresh();
    expect($stock->quantity)->toBe(100);
    expect($stock->reserved_quantity)->toBe(0);
});

test('order creation fails with insufficient stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => 50.00, 'product_id' => $product->id]);
    Stock::factory()->create(['variant_id' => $variant->id, 'quantity' => 2, 'reserved_quantity' => 0]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 5);

    $orderService = new OrderService;

    expect(fn () => $orderService->createOrderFromCart($cart->fresh(), $user, [
        'address_line' => '123 Test St',
        'city' => 'City',
        'postal_code' => '123',
        'country' => 'Country',
    ]))->toThrow(Exception::class);
});

test('order creation with coupon applies discount', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => 100.00, 'product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id, 'quantity' => 100]);
    $coupon = Coupon::factory()->create(['code' => 'DISC20', 'type' => 'fixed', 'value' => 20]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);
    $cartService->applyCoupon($cart, 'DISC20');

    $orderService = new OrderService;
    $order = $orderService->createOrderFromCart($cart->fresh(), $user, [
        'address_line' => '123 Test St',
        'city' => 'City',
        'postal_code' => '123',
        'country' => 'Country',
    ]);

    expect((float) $order->discount)->toEqual(20.00);
    expect((float) $order->total)->toEqual(80.00);
    expect($order->coupon_id)->toBe($coupon->id);
});

test('cart is cleared after order placement', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => 50.00, 'product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id, 'quantity' => 100]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    $orderService = new OrderService;
    $orderService->createOrderFromCart($cart->fresh(), $user, [
        'address_line' => '123 Test St',
        'city' => 'City',
        'postal_code' => '123',
        'country' => 'Country',
    ]);

    $cart = $cart->fresh();
    expect($cart->items)->toHaveCount(0);
    expect((float) $cart->subtotal)->toEqual(0.00);
});

test('direct order can be created without cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => 75.00, 'product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id, 'quantity' => 50]);

    $orderService = new OrderService;
    $order = $orderService->createDirectOrder($user, $variant->id, 2, [
        'address_line' => '789 Direct St',
        'city' => 'Direct City',
        'postal_code' => '99999',
        'country' => 'Direct Country',
    ]);

    expect($order)->not->toBeNull();
    expect($order->status)->toBe('pending');
    expect((float) $order->subtotal)->toEqual(150.00);
    expect((float) $order->total)->toEqual(150.00);
    expect($order->items)->toHaveCount(1);

    $stock = $variant->stock->fresh();
    expect($stock->quantity)->toBe(50);
    expect($stock->reserved_quantity)->toBe(2);
});

test('direct order fails with insufficient stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['price' => 50.00, 'product_id' => $product->id]);
    Stock::factory()->create(['variant_id' => $variant->id, 'quantity' => 1, 'reserved_quantity' => 0]);

    $orderService = new OrderService;

    expect(fn () => $orderService->createDirectOrder($user, $variant->id, 5, [
        'address_line' => '123 Test St',
        'city' => 'City',
        'postal_code' => '123',
        'country' => 'Country',
    ]))->toThrow(Exception::class);
});
