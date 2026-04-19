<?php

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use App\Services\CartService;
use Livewire\Livewire;

test('cart page loads', function () {
    $this->get(route('cart'))->assertOk();
});

test('cart page shows empty state when no items', function () {
    $this->get(route('cart'))
        ->assertOk()
        ->assertSee('Your cart is empty');
});

test('guest can add item to cart via service', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 29.99]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart(user: null, sessionId: 'test-session');
    $item = $cartService->addItem($cart, $variant->id, 2);

    expect($item->quantity)->toBe(2);
    expect($item->price)->toEqual('29.99');
    expect($item->total)->toEqual('59.98');
    expect((float) $cart->fresh()->subtotal)->toEqual(59.98);
});

test('user can add item to cart via service', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 50.00]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $item = $cartService->addItem($cart, $variant->id, 1);

    expect($item->quantity)->toBe(1);
    expect($item->variant_id)->toBe($variant->id);
    expect((float) $cart->fresh()->subtotal)->toEqual(50.00);
});

test('adding duplicate item merges quantities', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 20.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 2);
    $cartService->addItem($cart, $variant->id, 3);

    expect($cart->fresh()->items)->toHaveCount(1);
    expect($cart->fresh()->items->first()->quantity)->toBe(5);
    expect((float) $cart->fresh()->subtotal)->toEqual(100.00);
});

test('cart can apply valid coupon', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    $coupon = Coupon::factory()->create(['code' => 'SAVE10', 'type' => 'fixed', 'value' => 10]);

    $result = $cartService->applyCoupon($cart, 'SAVE10');

    expect($result)->toBeTrue();
    expect((float) $cart->fresh()->discount)->toEqual(10.00);
    expect((float) $cart->fresh()->total)->toEqual(90.00);
    expect($cart->fresh()->coupon_code)->toBe('SAVE10');
});

test('cart rejects expired coupon', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    Coupon::factory()->expired()->create(['code' => 'EXPIRED']);

    expect($cartService->applyCoupon($cart, 'EXPIRED'))->toBeFalse();
});

test('cart rejects non-existent coupon', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    expect($cartService->applyCoupon($cart, 'DOESNOTEXIST'))->toBeFalse();
});

test('cart rejects coupon with minimum order not met', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 25.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    Coupon::factory()->create(['code' => 'MIN50', 'type' => 'fixed', 'value' => 5, 'min_order_amount' => 50.00]);

    expect($cartService->applyCoupon($cart, 'MIN50'))->toBeFalse();
});

test('cart can remove coupon', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    Coupon::factory()->create(['code' => 'SAVE10', 'type' => 'fixed', 'value' => 10]);
    $cartService->applyCoupon($cart, 'SAVE10');

    $cartService->removeCoupon($cart);

    expect($cart->fresh()->coupon_code)->toBeNull();
    expect((float) $cart->fresh()->discount)->toEqual(0.00);
    expect((float) $cart->fresh()->total)->toEqual(100.00);
});

test('cart can update item quantity', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $item = $cartService->addItem($cart, $variant->id, 1);

    $cartService->updateItemQuantity($cart, $item->id, 3);

    expect($item->fresh()->quantity)->toBe(3);
    expect((float) $item->fresh()->total)->toEqual(30.00);
    expect((float) $cart->fresh()->subtotal)->toEqual(30.00);
});

test('cart removes item when quantity set to zero', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $item = $cartService->addItem($cart, $variant->id, 1);

    $cartService->updateItemQuantity($cart, $item->id, 0);

    expect($cart->fresh()->items)->toHaveCount(0);
    expect((float) $cart->fresh()->subtotal)->toEqual(0.00);
});

test('cart can remove individual item', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $item = $cartService->addItem($cart, $variant->id, 1);

    $cartService->removeItem($cart, $item->id);

    expect($cart->fresh()->items)->toHaveCount(0);
    expect((float) $cart->fresh()->subtotal)->toEqual(0.00);
});

test('cart can clear all items', function () {
    $user = User::factory()->create();
    $product1 = Product::factory()->create();
    $variant1 = ProductVariant::factory()->create(['product_id' => $product1->id, 'price' => 10.00]);
    $product2 = Product::factory()->create();
    $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id, 'price' => 20.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant1->id, 1);
    $cartService->addItem($cart, $variant2->id, 1);

    expect($cart->fresh()->items)->toHaveCount(2);

    $cartService->clearCart($cart);

    expect($cart->fresh()->items)->toHaveCount(0);
    expect((float) $cart->fresh()->subtotal)->toEqual(0.00);
    expect($cart->fresh()->coupon_code)->toBeNull();
});

test('cart can merge session cart to user cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 15.00]);

    $cartService = new CartService;

    // Create session cart
    $sessionCart = $cartService->getOrCreateCart(user: null, sessionId: 'session-123');
    $cartService->addItem($sessionCart, $variant->id, 2);

    // Create user cart with a different variant
    $product2 = Product::factory()->create();
    $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id, 'price' => 25.00]);
    $userCart = $cartService->getOrCreateCart($user);
    $cartService->addItem($userCart, $variant2->id, 1);

    // Merge
    $cartService->mergeSessionCartToUser($user, 'session-123');

    $userCart = $userCart->fresh();
    expect($userCart->items)->toHaveCount(2);

    // Session cart should be deleted
    expect(Cart::where('session_id', 'session-123')->exists())->toBeFalse();
});

test('cart merges quantities for same variant in session and user cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 15.00]);

    $cartService = new CartService;

    // Create session cart with item
    $sessionCart = $cartService->getOrCreateCart(user: null, sessionId: 'session-456');
    $cartService->addItem($sessionCart, $variant->id, 2);

    // Create user cart with same item
    $userCart = $cartService->getOrCreateCart($user);
    $cartService->addItem($userCart, $variant->id, 3);

    // Merge
    $cartService->mergeSessionCartToUser($user, 'session-456');

    $userCart = $userCart->fresh();
    expect($userCart->items)->toHaveCount(1);
    expect($userCart->items->first()->quantity)->toBe(5);
});

test('cart livewire component can apply coupon', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100.00]);

    $cartService = new CartService;
    $cart = $cartService->getOrCreateCart($user);
    $cartService->addItem($cart, $variant->id, 1);

    Coupon::factory()->create(['code' => 'TESTCODE', 'type' => 'fixed', 'value' => 10]);

    Livewire::actingAs($user)
        ->test('pages.cart')
        ->set('couponCode', 'TESTCODE')
        ->call('applyCoupon')
        ->assertSee('TESTCODE');
});

test('cart livewire component shows empty state', function () {
    Livewire::test('pages.cart')
        ->assertSee('Your cart is empty');
});
