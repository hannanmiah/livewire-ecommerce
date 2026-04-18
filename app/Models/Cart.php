<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'session_id', 'coupon_code', 'subtotal', 'discount', 'total', 'meta'])]
class Cart extends Model
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
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    /**
     * Get the user that owns the cart.
     *
     * @return BelongsTo<User, Cart>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the cart.
     *
     * @return HasMany<CartItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the coupon for the cart via coupon_code.
     *
     * @return BelongsTo<Coupon, Cart>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    /**
     * Recalculate the cart totals based on items and coupon.
     */
    public function recalculate(): void
    {
        $this->subtotal = (float) $this->items()->sum('total');

        $discount = 0.0;
        if ($this->coupon_code && $this->coupon) {
            $discount = $this->coupon->calculateDiscount((float) $this->subtotal);
        }

        $this->discount = $discount;
        $this->total = (float) $this->subtotal - $discount;
        $this->save();
    }
}
