<?php

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use App\Services\CouponService;

test('valid coupon can be applied', function () {
    $coupon = Coupon::factory()->create(['code' => 'VALID', 'type' => 'fixed', 'value' => 10]);

    $service = new CouponService;
    $result = $service->validateCoupon('VALID', 100.00, 1);

    expect($result['valid'])->toBeTrue();
    expect($result['coupon'])->not->toBeNull();
});

test('expired coupon cannot be validated', function () {
    Coupon::factory()->expired()->create(['code' => 'OLD']);

    $service = new CouponService;
    $result = $service->validateCoupon('OLD', 100.00, 1);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toBe('Coupon has expired or reached its usage limit.');
});

test('non-existent coupon returns not found', function () {
    $service = new CouponService;
    $result = $service->validateCoupon('NOCODE', 100.00, 1);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toBe('Coupon not found.');
});

test('coupon with minimum order not met returns error', function () {
    Coupon::factory()->create(['code' => 'MIN50', 'type' => 'fixed', 'value' => 5, 'min_order_amount' => 50.00]);

    $service = new CouponService;
    $result = $service->validateCoupon('MIN50', 25.00, 1);

    expect($result['valid'])->toBeFalse();
    expect($result['error'])->toBe('Minimum order amount not met.');
});

test('coupon with usage limit tracks usage', function () {
    $coupon = Coupon::factory()->create(['usage_limit' => 1, 'used_count' => 0]);
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $variant->id]);
    $order = Order::factory()->create(['user_id' => $user->id, 'coupon_id' => $coupon->id]);

    $service = new CouponService;
    $service->recordUsage($coupon, $user, $order);

    expect($coupon->fresh()->used_count)->toBe(1);
    expect(CouponUsage::count())->toBe(1);

    $usage = CouponUsage::first();
    expect($usage->coupon_id)->toBe($coupon->id);
    expect($usage->user_id)->toBe($user->id);
    expect($usage->order_id)->toBe($order->id);
});

test('coupon usage limit reached becomes invalid', function () {
    $coupon = Coupon::factory()->create(['usage_limit' => 1, 'used_count' => 1]);

    expect($coupon->isValid())->toBeFalse();
});

test('coupon with no usage limit stays valid after uses', function () {
    $coupon = Coupon::factory()->create(['usage_limit' => null, 'used_count' => 100]);

    expect($coupon->isValid())->toBeTrue();
});

test('coupon with no expiry stays valid', function () {
    $coupon = Coupon::factory()->create(['expires_at' => null]);

    expect($coupon->isValid())->toBeTrue();
});

test('percent coupon calculates correct discount', function () {
    $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 20]);

    expect($coupon->calculateDiscount(100.00))->toEqual(20.00);
});

test('percent coupon with different subtotal', function () {
    $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 15]);

    expect($coupon->calculateDiscount(200.00))->toEqual(30.00);
});

test('fixed coupon calculates correct discount', function () {
    $coupon = Coupon::factory()->create(['type' => 'fixed', 'value' => 15]);

    expect($coupon->calculateDiscount(100.00))->toEqual(15.00);
});

test('fixed coupon discount cannot exceed subtotal', function () {
    $coupon = Coupon::factory()->create(['type' => 'fixed', 'value' => 150]);

    expect($coupon->calculateDiscount(100.00))->toEqual(100.00);
});

test('calculateDiscount via service returns correct value', function () {
    $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 25]);

    $service = new CouponService;
    $discount = $service->calculateDiscount($coupon, 200.00);

    expect($discount)->toEqual(50.00);
});
