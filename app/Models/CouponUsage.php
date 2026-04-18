<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['coupon_id', 'user_id', 'order_id', 'used_at'])]
class CouponUsage extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    /**
     * Get the coupon that owns the coupon usage.
     *
     * @return BelongsTo<Coupon, CouponUsage>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the user that owns the coupon usage.
     *
     * @return BelongsTo<User, CouponUsage>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order that owns the coupon usage.
     *
     * @return BelongsTo<Order, CouponUsage>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
