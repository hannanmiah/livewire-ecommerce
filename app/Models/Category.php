<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mattiverse\Userstamps\Traits\Userstamps;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

#[Fillable(['name', 'slug', 'parent_id', 'featured_at'])]
class Category extends Model
{
    use HasFactory, HasRecursiveRelationships, Sluggable, Userstamps;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'featured_at' => 'datetime',
        ];
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array<string, mixed>
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    /**
     * Determine if the category is featured.
     */
    public function getIsFeaturedAttribute(): bool
    {
        return $this->featured_at !== null && $this->featured_at->isPast();
    }

    /**
     * Get the products for the category.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the products that belong to this category via the pivot table.
     *
     * @return BelongsToMany<Product>
     */
    public function productsIn(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
