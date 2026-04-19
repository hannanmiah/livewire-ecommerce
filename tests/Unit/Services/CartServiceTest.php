<?php

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;

test('cart recalculation computes correct subtotal', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 25.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 3);

    $cart = $cart->fresh();
    expect((float) $cart->subtotal)->toEqual(75.00);
    expect((float) $cart->total)->toEqual(75.00);
    expect((float) $cart->discount)->toEqual(0.00);
});

test('cart recalculation with fixed coupon applies discount', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100.00]);
    Coupon::factory()->create(['code' => 'FIX10', 'type' => 'fixed', 'value' => 10]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);
    $cartService->applyCoupon($cart, 'FIX10');

    $cart = $cart->fresh();
    expect((float) $cart->subtotal)->toEqual(100.00);
    expect((float) $cart->discount)->toEqual(10.00);
    expect((float) $cart->total)->toEqual(90.00);
});

test('cart recalculation with percent coupon applies discount', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 200.00]);
    Coupon::factory()->create(['code' => 'PCT25', 'type' => 'percent', 'value' => 25]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);
    $cartService->applyCoupon($cart, 'PCT25');

    $cart = $cart->fresh();
    expect((float) $cart->subtotal)->toEqual(200.00);
    expect((float) $cart->discount)->toEqual(50.00);
    expect((float) $cart->total)->toEqual(150.00);
});

test('cart recalculation after removing item updates totals', function () {
    $user = User::factory()->create();
    $product1 = Product::factory()->create();
    $variant1 = ProductVariant::factory()->create(['product_id' => $product1->id, 'price' => 30.00]);
    $product2 = Product::factory()->create();
    $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id, 'price' => 20.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant1->id, 1);
    $item2 = $cartService->addItem($cart, $variant2->id, 2);

    $cart = $cart->fresh();
    expect((float) $cart->subtotal)->toEqual(70.00);

    $cartService->removeItem($cart, $item2->id);

    $cart = $cart->fresh();
    expect((float) $cart->subtotal)->toEqual(30.00);
    expect((float) $cart->total)->toEqual(30.00);
});

test('adding duplicate item merges quantities correctly', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 15.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 2);
    $cartService->addItem($cart, $variant->id, 3);

    $cart = $cart->fresh();
    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->quantity)->toBe(5);
    expect((float) $cart->subtotal)->toEqual(75.00);
});

test('merging session cart to user cart preserves both sets of items', function () {
    $user = User::factory()->create();
    $product1 = Product::factory()->create();
    $variant1 = ProductVariant::factory()->create(['product_id' => $product1->id, 'price' => 10.00]);
    $product2 = Product::factory()->create();
    $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id, 'price' => 20.00]);

    $cartService = new CartService;

    // Session cart
    $sessionCart = $cartService->getOrCreateCart(user: null, sessionId: 'merge-session');
    $cartService->addItem($sessionCart, $variant1->id, 2);

    // User cart
    $userCart = $cartService->getOrCreateCart($user);
    $cartService->addItem($userCart, $variant2->id, 1);

    // Merge
    $cartService->mergeSessionCartToUser($user, 'merge-session');

    $userCart = $userCart->fresh();
    expect($userCart->items)->toHaveCount(2);
    expect((float) $userCart->subtotal)->toEqual(40.00);

    // Session cart deleted
    expect(Cart::where('session_id', 'merge-session')->exists())->toBeFalse();
});

test('merging session cart with overlapping variant merges quantities', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10.00]);

    $cartService = new CartService;

    // Session cart
    $sessionCart = $cartService->getOrCreateCart(user: null, sessionId: 'overlap-session');
    $cartService->addItem($sessionCart, $variant->id, 3);

    // User cart with same variant
    $userCart = $cartService->getOrCreateCart($user);
    $cartService->addItem($userCart, $variant->id, 2);

    // Merge
    $cartService->mergeSessionCartToUser($user, 'overlap-session');

    $userCart = $userCart->fresh();
    expect($userCart->items)->toHaveCount(1);
    expect($userCart->items->first()->quantity)->toBe(5);
    expect((float) $userCart->subtotal)->toEqual(50.00);
});

test('merging non-existent session cart does nothing', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10.00]);

    $cartService = new CartService;
    $userCart = $cartService->getOrCreateCart($user);
    $cartService->addItem($userCart, $variant->id, 1);

    // Merge with non-existent session
    $cartService->mergeSessionCartToUser($user, 'nonexistent-session');

    $userCart = $userCart->fresh();
    expect($userCart->items)->toHaveCount(1);
});

test('getOrCreateCart returns existing cart for user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10.00]);

    $cartService = new CartService;
    $cart1 = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart1, $variant->id, 1);

    $cart2 = $cartService->getOrCreateCart($user);

    expect($cart2->id)->toBe($cart1->id);
});

test('getOrCreateCart returns existing cart for session', function () {
    $cartService = new CartService;
    $cart1 = $cartService->getOrCreateCart(user: null, sessionId: 'same-session');
    $cart2 = $cartService->getOrCreateCart(user: null, sessionId: 'same-session');

    expect($cart2->id)->toBe($cart1->id);
});

test('getCartItemCount returns zero for no cart', function () {
    $cartService = new CartService;
    expect($cartService->getCartItemCount(user: null, sessionId: 'empty-session'))->toBe(0);
});

test('getCartItemCount returns correct count', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    expect($cartService->getCartItemCount($user))->toBe(1);
});
