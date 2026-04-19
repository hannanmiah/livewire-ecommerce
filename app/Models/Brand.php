<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mattiverse\Userstamps\Traits\Userstamps;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['name', 'slug', 'featured_at'])]
class Brand extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Sluggable, Userstamps;

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
     * Register the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')->singleFile();
    }

    /**
     * Register media conversion
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100)
            ->sharpen(10);
    }

    /**
     * Determine if the brand is featured.
     */
    public function isFeatured(): Attribute
    {
        return Attribute::get(fn () => $this->featured_at !== null && $this->featured_at->isPast())->shouldCache();
    }

    /**
     * logo attribute
     */
    public function logo(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl())->shouldCache();
    }

    /**
     * Get the products for the brand.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Featured scope
     */
    #[Scope]
    public function featured(Builder $query)
    {
        $query->whereNotNull('featured_at')->where('featured_at', '<=', now());
    }
}
