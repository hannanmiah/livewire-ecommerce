<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[Fillable(['product_id', 'sku', 'price', 'sale_price', 'weight'])]
class ProductVariant extends Model
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
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'weight' => 'decimal:2',
        ];
    }

    /**
     * Get the effective price (sale price if on sale, otherwise regular price).
     */
    public function effectivePrice(): Attribute
    {
        return Attribute::get(fn() => $this->sale_price ?? $this->price)->shouldCache();
    }

    /**
     * Determine if the variant is on sale.
     */
    public function isOnSale(): Attribute
    {
        return Attribute::get(fn() => $this->sale_price && $this->sale_price < $this->price)->shouldCache();
    }

    /**
     * Get the product that owns the variant.
     *
     * @return BelongsTo<Product, ProductVariant>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the stock for the variant.
     *
     * @return HasOne<Stock>
     */
    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class, 'variant_id');
    }

    /**
     * Get the attribute values for the variant.
     *
     * @return BelongsToMany<AttributeValue>
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_value', 'variant_id', 'attribute_value_id');
    }
}
