<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['variant_id', 'quantity', 'reserved_quantity'])]
class Stock extends Model
{
    use HasFactory;

    /**
     * Get the available quantity (total minus reserved).
     */
    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    /**
     * Determine if the stock is in stock.
     */
    public function isInStock(): bool
    {
        return $this->available_quantity > 0;
    }

    /**
     * Get the variant that owns the stock.
     *
     * @return BelongsTo<ProductVariant, Stock>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
