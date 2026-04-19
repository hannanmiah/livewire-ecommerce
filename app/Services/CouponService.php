<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Validate a coupon code for the given subtotal and user.
     *
     * @return array{valid: bool, error?: string, coupon?: Coupon}
     */
    public function validateCoupon(string $code, float $subtotal, int $userId): array
    {
        $coupon = Coupon::where('code', $code)->lockForUpdate()->first();

        if (! $coupon) {
            return ['valid' => false, 'error' => 'Coupon not found.'];
        }

        if (! $coupon->isValid()) {
            return ['valid' => false, 'error' => 'Coupon has expired or reached its usage limit.'];
        }

        if ($coupon->min_order_amount && $subtotal < (float) $coupon->min_order_amount) {
            return ['valid' => false, 'error' => 'Minimum order amount not met.'];
        }

        return ['valid' => true, 'coupon' => $coupon];
    }

    /**
     * Calculate the discount amount for the given coupon and subtotal.
     */
    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        return $coupon->calculateDiscount($subtotal);
    }

    /**
     * Record coupon usage within a locked transaction.
     */
    public function recordUsage(Coupon $coupon, User $user, Order $order): void
    {
        DB::transaction(function () use ($coupon, $user, $order): void {
            $coupon = Coupon::lockForUpdate()->find($coupon->id);
            $coupon->increment('used_count');

            CouponUsage::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'used_at' => now(),
            ]);
        });
    }
}
