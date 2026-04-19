# Ecommerce Platform Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a complete single-vendor ecommerce application with admin panel, public storefront, cart/checkout, customer dashboard, and comprehensive tests.

**Architecture:** Laravel 13 backend with Livewire 4 SFC components for all interactivity. Full-page Livewire components for pages, inline SFC components for interactive elements. Flux UI for component library. Service classes in `app/Services` for complex business logic. SQLite for development.

**Tech Stack:** PHP 8.4, Laravel 13, Livewire 4, Flux UI 2, Tailwind CSS 4, Pest 4, spatie/laravel-medialibrary, staudenmeir/laravel-adjacency-list, cviebrock/eloquent-sluggable, wildside/userstamps

---

## Phase 1: Foundation (Migrations, Models, Packages, Factories, Seeders)

### Task 1.1: Install External Packages

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Install required composer packages**

Run:
```bash
composer require spatie/laravel-medialibrary staudenmeir/laravel-adjacency-list cviebrock/eloquent-sluggable wildside/userstamps
```

- [ ] **Step 2: Publish media library migration**

Run:
```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations" --no-interaction
```

- [ ] **Step 3: Verify installations**

Run: `php artisan migrate --pretend`
Expected: No errors. Packages discovered successfully.

- [ ] **Step 4: Commit**

```bash
git add composer.json composer.lock
git commit -m "feat: install external packages (medialibrary, adjacency-list, sluggable, userstamps)"
```

---

### Task 1.2: Add Phone and Role Columns to Users Table

**Files:**
- Create: `database/migrations/2026_04_19_000001_add_phone_role_to_users_table.php`

- [ ] **Step 1: Create migration**

Run:
```bash
php artisan make:migration add_phone_role_to_users_table --no-interaction
```

- [ ] **Step 2: Write migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('role')->default('customer')->after('phone'); // admin, editor, customer
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role']);
        });
    }
};
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: Migration runs without errors.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/
git commit -m "feat: add phone and role columns to users table"
```

---

### Task 1.3: Create Categories Migration

**Files:**
- Create: `database/migrations/2026_04_19_000002_create_categories_table.php`

- [ ] **Step 1: Create migration**

Run:
```bash
php artisan make:migration create_categories_table --no-interaction
```

- [ ] **Step 2: Write migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->dateTime('featured_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`

- [ ] **Step 4: Commit**

```bash
git add database/migrations/
git commit -m "feat: create categories table migration"
```

---

### Task 1.4: Create Brands Migration

**Files:**
- Create: `database/migrations/2026_04_19_000003_create_brands_table.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->dateTime('featured_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
```

- [ ] **Step 2: Run migration**

- [ ] **Step 3: Commit**

```bash
git add database/migrations/
git commit -m "feat: create brands table migration"
```

---

### Task 1.5: Create Products and Category Product Pivot Migrations

**Files:**
- Create: `database/migrations/2026_04_19_000004_create_products_table.php`
- Create: `database/migrations/2026_04_19_000005_create_category_product_table.php`

- [ ] **Step 1: Create products migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->dateTime('featured_at')->nullable();
            $table->dateTime('available_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

- [ ] **Step 2: Create category_product pivot migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
```

- [ ] **Step 3: Run migrations**

- [ ] **Step 4: Commit**

```bash
git add database/migrations/
git commit -m "feat: create products and category_product tables"
```

---

### Task 1.6: Create Attributes and Attribute Values Migrations

**Files:**
- Create: `database/migrations/2026_04_19_000006_create_attributes_table.php`
- Create: `database/migrations/2026_04_19_000007_create_attribute_values_table.php`

- [ ] **Step 1: Create attributes migration**

```php
Schema::create('attributes', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});
```

- [ ] **Step 2: Create attribute_values migration**

```php
Schema::create('attribute_values', function (Blueprint $table) {
    $table->id();
    $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
    $table->string('value');
    $table->timestamps();
});
```

- [ ] **Step 3: Run migrations and commit**

---

### Task 1.7: Create Product Variants and Variant Attribute Value Pivot Migrations

**Files:**
- Create: `database/migrations/2026_04_19_000008_create_product_variants_table.php`
- Create: `database/migrations/2026_04_19_000009_create_variant_attribute_value_table.php`

- [ ] **Step 1: Create product_variants migration**

```php
Schema::create('product_variants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->string('sku')->unique();
    $table->decimal('price', 10, 2);
    $table->decimal('sale_price', 10, 2)->nullable();
    $table->decimal('weight', 8, 2)->nullable();
    $table->timestamps();
});
```

- [ ] **Step 2: Create variant_attribute_value pivot migration**

```php
Schema::create('variant_attribute_value', function (Blueprint $table) {
    $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
    $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
    $table->primary(['variant_id', 'attribute_value_id']);
});
```

- [ ] **Step 3: Run migrations and commit**

---

### Task 1.8: Create Stocks Migration

**Files:**
- Create: `database/migrations/2026_04_19_000010_create_stocks_table.php`

```php
Schema::create('stocks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
    $table->integer('quantity')->default(0);
    $table->integer('reserved_quantity')->default(0);
    $table->timestamps();
});
```

---

### Task 1.9: Create Coupons Migration

**Files:**
- Create: `database/migrations/2026_04_19_000011_create_coupons_table.php`

```php
Schema::create('coupons', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->enum('type', ['fixed', 'percent']);
    $table->decimal('value', 10, 2);
    $table->decimal('min_order_amount', 10, 2)->nullable();
    $table->integer('usage_limit')->nullable();
    $table->integer('used_count')->default(0);
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});
```

---

### Task 1.10: Create Addresses Migration

**Files:**
- Create: `database/migrations/2026_04_19_000012_create_addresses_table.php`

```php
Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('type');
    $table->string('address_line');
    $table->string('city');
    $table->string('postal_code')->nullable();
    $table->string('country');
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});
```

---

### Task 1.11: Create Orders Migration

**Files:**
- Create: `database/migrations/2026_04_19_000013_create_orders_table.php`

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('order_number')->unique();
    $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled'])->default('pending');
    $table->enum('payment_status', ['unpaid', 'paid', 'failed'])->default('unpaid');
    $table->enum('shipping_status', ['pending', 'shipped', 'delivered'])->default('pending');
    $table->decimal('subtotal', 10, 2);
    $table->decimal('shipping_fee', 10, 2)->default(0);
    $table->decimal('discount', 10, 2)->default(0);
    $table->decimal('total', 10, 2);
    $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamps();
});
```

---

### Task 1.12: Create Order Items Migration

**Files:**
- Create: `database/migrations/2026_04_19_000014_create_order_items_table.php`

```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
    $table->string('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->integer('quantity');
    $table->decimal('total', 10, 2);
    $table->json('variant_attributes')->nullable();
    $table->timestamps();
});
```

---

### Task 1.13: Create Payments Migration

**Files:**
- Create: `database/migrations/2026_04_19_000015_create_payments_table.php`

```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->string('method');
    $table->decimal('amount', 10, 2);
    $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
    $table->string('transaction_id')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
});
```

---

### Task 1.14: Create Coupon Usages Migration

**Files:**
- Create: `database/migrations/2026_04_19_000016_create_coupon_usages_table.php`

```php
Schema::create('coupon_usages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->timestamp('used_at')->nullable();
    $table->timestamps();
});
```

---

### Task 1.15: Create Carts and Cart Items Migrations

**Files:**
- Create: `database/migrations/2026_04_19_000017_create_carts_table.php`
- Create: `database/migrations/2026_04_19_000018_create_cart_items_table.php`

```php
// carts
Schema::create('carts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('session_id')->nullable();
    $table->string('coupon_code')->nullable();
    $table->decimal('subtotal', 10, 2)->default(0);
    $table->decimal('discount', 10, 2)->default(0);
    $table->decimal('total', 10, 2)->default(0);
    $table->json('meta')->nullable();
    $table->timestamps();
});

// cart_items
Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
    $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
    $table->string('description')->nullable();
    $table->integer('quantity');
    $table->decimal('price', 10, 2);
    $table->decimal('total', 10, 2)->default(0);
    $table->timestamps();
});
```

---

### Task 1.16: Create Reviews Migration

**Files:**
- Create: `database/migrations/2026_04_19_000019_create_reviews_table.php`

```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->tinyInteger('rating');
    $table->text('comment')->nullable();
    $table->timestamps();
});
```

---

### Task 1.17: Create Banners Migration

**Files:**
- Create: `database/migrations/2026_04_19_000020_create_banners_table.php`

```php
Schema::create('banners', function (Blueprint $table) {
    $table->id();
    $table->string('category');
    $table->string('title');
    $table->string('slug')->unique();
    $table->string('link')->nullable();
    $table->text('description')->nullable();
    $table->string('position')->nullable();
    $table->dateTime('featured_at')->nullable();
    $table->timestamps();
});
```

---

### Task 1.18: Run All Remaining Migrations

- [ ] **Step 1: Run all pending migrations**

Run: `php artisan migrate`
Expected: All tables created successfully.

- [ ] **Step 2: Verify with schema check**

Run: `php artisan tinker --execute 'echo implode(", ", Schema::getTableListing());'`
Expected: All table names listed.

- [ ] **Step 3: Commit all migrations**

```bash
git add database/migrations/
git commit -m "feat: create all ecommerce table migrations"
```

---

### Task 1.19: Update User Model

**Files:**
- Modify: `app/Models/User.php`

- [ ] **Step 1: Update User model with new relationships and role functionality**

```php
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Wildside\Userstamps\Userstamps;

#[Fillable(['name', 'email', 'password', 'phone', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, Userstamps;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
```

- [ ] **Step 2: Update UserFactory to include new fields**

Update `database/factories/UserFactory.php` definition to include:
```php
'phone' => fake()->phoneNumber(),
'role' => 'customer',
```

- [ ] **Step 3: Commit**

```bash
git add app/Models/User.php database/factories/UserFactory.php
git commit -m "feat: update User model with role, relationships, and userstamps"
```

---

### Task 1.20: Create Category Model

**Files:**
- Create: `app/Models/Category.php`
- Create: `database/factories/CategoryFactory.php`

- [ ] **Step 1: Create model with factory**

Run: `php artisan make:model Category --no-interaction`

- [ ] **Step 2: Write Category model**

```php
<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
use Wildside\Userstamps\Userstamps;

class Category extends Model
{
    use HasFactory, HasRecursiveRelationships, Sluggable, Userstamps;

    protected function casts(): array
    {
        return [
            'featured_at' => 'datetime',
        ];
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    public function getIsFeaturedAttribute(): bool
    {
        return $this->featured_at !== null && $this->featured_at->isPast();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productsIn()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
```

- [ ] **Step 3: Create CategoryFactory**

```php
<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'featured_at' => null,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured_at' => now(),
        ]);
    }
}
```

- [ ] **Step 4: Commit**

---

### Task 1.21: Create Brand Model

**Files:**
- Create: `app/Models/Brand.php`
- Create: `database/factories/BrandFactory.php`

```php
// Brand model
<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class Brand extends Model
{
    use HasFactory, Sluggable, Userstamps;

    protected function casts(): array
    {
        return [
            'featured_at' => 'datetime',
        ];
    }

    public function sluggable(): array
    {
        return ['slug' => ['source' => 'name']];
    }

    public function getIsFeaturedAttribute(): bool
    {
        return $this->featured_at !== null && $this->featured_at->isPast();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
```

---

### Task 1.22: Create Product Model

**Files:**
- Create: `app/Models/Product.php`
- Create: `database/factories/ProductFactory.php`

```php
<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Wildside\Userstamps\Userstamps;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Sluggable, Userstamps;

    protected function casts(): array
    {
        return [
            'featured_at' => 'datetime',
            'available_at' => 'datetime',
        ];
    }

    public function sluggable(): array
    {
        return ['slug' => ['source' => 'name']];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function getIsFeaturedAttribute(): bool
    {
        return $this->featured_at !== null && $this->featured_at->isPast();
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->available_at !== null && $this->available_at->isPast();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeAvailable($query)
    {
        return $query->whereNotNull('available_at')->where('available_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->whereNotNull('featured_at')->where('featured_at', '<=', now());
    }
}
```

---

### Task 1.23: Create Attribute and AttributeValue Models

**Files:**
- Create: `app/Models/Attribute.php`
- Create: `app/Models/AttributeValue.php`

```php
// Attribute
class Attribute extends Model
{
    use HasFactory;

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}

// AttributeValue
class AttributeValue extends Model
{
    use HasFactory;

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'variant_attribute_value');
    }
}
```

---

### Task 1.24: Create ProductVariant Model

**Files:**
- Create: `app/Models/ProductVariant.php`
- Create: `database/factories/ProductVariantFactory.php`

```php
class ProductVariant extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'weight' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_value');
    }

    public function getEffectivePriceAttribute(): string
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }
}
```

---

### Task 1.25: Create Stock Model

**Files:**
- Create: `app/Models/Stock.php`

```php
class Stock extends Model
{
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function isInStock(): bool
    {
        return $this->available_quantity > 0;
    }
}
```

---

### Task 1.26: Create Coupon Model

**Files:**
- Create: `app/Models/Coupon.php`
- Create: `database/factories/CouponFactory.php`

```php
class Coupon extends Model
{
    use HasFactory, Userstamps;

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'fixed') {
            return min((float) $this->value, $subtotal);
        }

        return round($subtotal * ((float) $this->value / 100), 2);
    }
}
```

---

### Task 1.27: Create Address Model

**Files:**
- Create: `app/Models/Address.php`
- Create: `database/factories/AddressFactory.php`

```php
class Address extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

---

### Task 1.28: Create Order and OrderItem Models

**Files:**
- Create: `app/Models/Order.php`
- Create: `app/Models/OrderItem.php`

```php
// Order
class Order extends Model
{
    use HasFactory, Userstamps;

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number = 'ORD-' . strtoupper(Str::random(10));
        });
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payment() { return $this->hasOne(Payment::class); }
    public function coupon() { return $this->belongsTo(Coupon::class); }
}

// OrderItem
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'total' => 'decimal:2',
            'variant_attributes' => 'array',
        ];
    }

    public function order() { return $this->belongsTo(Order::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class); }
}
```

---

### Task 1.29: Create Payment Model

**Files:**
- Create: `app/Models/Payment.php`

```php
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order() { return $this->belongsTo(Order::class); }
}
```

---

### Task 1.30: Create CouponUsage Model

**Files:**
- Create: `app/Models/CouponUsage.php`

```php
class CouponUsage extends Model
{
    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    public function coupon() { return $this->belongsTo(Coupon::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
```

---

### Task 1.31: Create Cart and CartItem Models

**Files:**
- Create: `app/Models/Cart.php`
- Create: `app/Models/CartItem.php`

```php
// Cart
class Cart extends Model
{
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(CartItem::class); }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    public function recalculate(): void
    {
        $this->subtotal = $this->items->sum('total');
        $this->discount = 0;

        if ($this->coupon && $this->coupon->isValid()) {
            $this->discount = $this->coupon->calculateDiscount((float) $this->subtotal);
        }

        $this->total = $this->subtotal - $this->discount;
        $this->save();
    }
}

// CartItem
class CartItem extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function cart() { return $this->belongsTo(Cart::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class); }

    protected static function booted(): void
    {
        static::saving(function (CartItem $item) {
            $item->total = $item->price * $item->quantity;
        });
    }
}
```

---

### Task 1.32: Create Review Model

**Files:**
- Create: `app/Models/Review.php`

```php
class Review extends Model
{
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
}
```

---

### Task 1.33: Create Banner Model

**Files:**
- Create: `app/Models/Banner.php`
- Create: `database/factories/BannerFactory.php`

```php
class Banner extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Sluggable, Userstamps;

    public function sluggable(): array
    {
        return ['slug' => ['source' => 'title']];
    }

    protected function casts(): array
    {
        return [
            'featured_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function getIsFeaturedAttribute(): bool
    {
        return $this->featured_at !== null && $this->featured_at->isPast();
    }
}
```

---

### Task 1.34: Create All Remaining Factories

**Files:**
- Create: `database/factories/ProductFactory.php`
- Create: `database/factories/ProductVariantFactory.php`
- Create: `database/factories/AttributeFactory.php`
- Create: `database/factories/AttributeValueFactory.php`
- Create: `database/factories/CouponFactory.php`
- Create: `database/factories/AddressFactory.php`
- Create: `database/factories/OrderFactory.php`
- Create: `database/factories/OrderItemFactory.php`
- Create: `database/factories/PaymentFactory.php`
- Create: `database/factories/ReviewFactory.php`
- Create: `database/factories/CartFactory.php`
- Create: `database/factories/CartItemFactory.php`
- Create: `database/factories/StockFactory.php`

Each factory should follow the UserFactory pattern. Key factory states:
- **ProductFactory**: `featured()` state, `available()` state
- **CouponFactory**: `expired()` state, `percent()` state, `fixed()` state
- **UserFactory**: add `admin()` state returning `['role' => 'admin']`

- [ ] **Step: Create all factories and commit**

---

### Task 1.35: Create Database Seeder

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`

Create a comprehensive seeder that creates:
- 1 admin user (admin@example.com)
- 10 categories (3 featured, some with children)
- 5 brands (2 featured)
- 3 attributes (Size, Color, Material) with values each
- 50 products (10 featured, all available) with variants, stocks
- 3 banners (1 hero, 2 featured sections)
- 2 coupons (1 fixed, 1 percent)

- [ ] **Step: Write seeder and commit**

---

### Task 1.36: Run Pint on All PHP Files

- [ ] **Step: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step: Run tests to ensure nothing is broken**

Run: `php artisan test --compact`

- [ ] **Step: Commit all foundation code**

```bash
git add .
git commit -m "feat: complete foundation - all models, factories, seeders, migrations"
```

---

## Phase 2: Admin Panel

### Task 2.1: Create Admin Middleware and Routes

**Files:**
- Create: `app/Http/Middleware/EnsureUserIsAdmin.php`
- Create: `routes/admin.php`
- Modify: `bootstrap/app.php` (register middleware + routes)

- [ ] **Step 1: Create admin middleware**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAdmin() !== true) {
            abort(403);
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Create admin routes file**

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.⚡dashboard')->name('dashboard');

    // Products
    Route::livewire('products', 'admin.products.index')->name('products.index');
    Route::livewire('products/create', 'admin.products.create')->name('products.create');
    Route::livewire('products/{product}/edit', 'admin.products.edit')->name('products.edit');

    // Categories
    Route::livewire('categories', 'admin.categories.index')->name('categories.index');
    Route::livewire('categories/create', 'admin.categories.create')->name('categories.create');
    Route::livewire('categories/{category}/edit', 'admin.categories.edit')->name('categories.edit');

    // Brands
    Route::livewire('brands', 'admin.brands.index')->name('brands.index');
    Route::livewire('brands/create', 'admin.brands.create')->name('brands.create');
    Route::livewire('brands/{brand}/edit', 'admin.brands.edit')->name('brands.edit');

    // Orders
    Route::livewire('orders', 'admin.orders.index')->name('orders.index');
    Route::livewire('orders/{order}', 'admin.orders.show')->name('orders.show');

    // Coupons
    Route::livewire('coupons', 'admin.coupons.index')->name('coupons.index');
    Route::livewire('coupons/create', 'admin.coupons.create')->name('coupons.create');
    Route::livewire('coupons/{coupon}/edit', 'admin.coupons.edit')->name('coupons.edit');

    // Banners
    Route::livewire('banners', 'admin.banners.index')->name('banners.index');
    Route::livewire('banners/create', 'admin.banners.create')->name('banners.create');
    Route::livewire('banners/{banner}/edit', 'admin.banners.edit')->name('banners.edit');

    // Users
    Route::livewire('users', 'admin.users.index')->name('users.index');
    Route::livewire('users/{user}', 'admin.users.show')->name('users.show');
});
```

- [ ] **Step 3: Register middleware alias and routes in bootstrap/app.php**

Add `'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class` to middleware aliases.
Add `require __DIR__.'/../routes/admin.php';` to web middleware group.

- [ ] **Step 4: Create admin dashboard view**

Create `resources/views/admin/dashboard.blade.php` extending the sidebar layout with links to all admin sections.

- [ ] **Step 5: Create admin layout**

Create `resources/views/layouts/admin.blade.php` — a sidebar layout with admin navigation menu (Products, Categories, Brands, Orders, Coupons, Banners, Users).

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "feat: add admin middleware, routes, and layout"
```

---

### Task 2.2: Admin Categories CRUD

**Files:**
- Create: `resources/views/livewire/admin/categories/index.blade.php`
- Create: `resources/views/livewire/admin/categories/create.blade.php`
- Create: `resources/views/livewire/admin/categories/edit.blade.php`

Each is a Livewire 4 SFC (full page component) with:
- **Index**: Flux table listing categories with name, slug, parent, featured status. Actions: edit, delete. Search/filter.
- **Create**: Form with name input (slug auto-generated via sluggable), parent category select, featured toggle.
- **Edit**: Same as create, pre-filled. Includes delete button.

Use Flux components: `flux:table`, `flux:input`, `flux:select`, `flux:button`, `flux:modal` for delete confirmation.

---

### Task 2.3: Admin Brands CRUD

**Files:**
- Create: `resources/views/livewire/admin/brands/index.blade.php`
- Create: `resources/views/livewire/admin/brands/create.blade.php`
- Create: `resources/views/livewire/admin/brands/edit.blade.php`

Similar structure to Categories but simpler (no parent relationship). Fields: name, slug, featured toggle.

---

### Task 2.4: Admin Products CRUD (with Variants, Stock, Images)

**Files:**
- Create: `resources/views/livewire/admin/products/index.blade.php`
- Create: `resources/views/livewire/admin/products/create.blade.php`
- Create: `resources/views/livewire/admin/products/edit.blade.php`

**Index**: Table listing products with thumbnail, name, category, brand, price range, featured/available status. Filters by category, brand, search.

**Create/Edit**: Complex form with:
- Basic info: name, description, category (primary), brand, additional categories (multi-select), featured toggle, available toggle
- Image upload: thumbnail (single) and gallery (multiple) using medialibrary
- Variants section: add/edit/delete variants inline, each with SKU, price, sale_price, weight, attribute values selection
- Stock section: per-variant stock quantity

Use `WithFileUploads` trait for image handling. Use `flux:input`, `flux:select`, `flux:textarea`, `flux:checkbox`, `flux:modal`.

---

### Task 2.5: Admin Orders Management

**Files:**
- Create: `resources/views/livewire/admin/orders/index.blade.php`
- Create: `resources/views/livewire/admin/orders/show.blade.php`

**Index**: Table with order number, customer name, status badges, total, date. Filters by status. Bulk status update.

**Show**: Detailed view with order items, payment info, shipping status update form.

---

### Task 2.6: Admin Coupons CRUD

**Files:**
- Create: `resources/views/livewire/admin/coupons/index.blade.php`
- Create: `resources/views/livewire/admin/coupons/create.blade.php`
- Create: `resources/views/livewire/admin/coupons/edit.blade.php`

Fields: code, type (fixed/percent), value, min_order_amount, usage_limit, expires_at.

---

### Task 2.7: Admin Banners CRUD

**Files:**
- Create: `resources/views/livewire/admin/banners/index.blade.php`
- Create: `resources/views/livewire/admin/banners/create.blade.php`
- Create: `resources/views/livewire/admin/banners/edit.blade.php`

Fields: title, category, position, link, description, featured toggle, image upload (medialibrary).

---

### Task 2.8: Admin Users Management

**Files:**
- Create: `resources/views/livewire/admin/users/index.blade.php`
- Create: `resources/views/livewire/admin/users/show.blade.php`

**Index**: Table with name, email, role, orders count. Role filter.

**Show**: User details, role update form, order history.

---

### Task 2.9: Create Admin Policies

**Files:**
- Create: `app/Policies/ProductPolicy.php`
- Create: `app/Policies/CategoryPolicy.php`
- Create: `app/Policies/BrandPolicy.php`
- Create: `app/Policies/OrderPolicy.php`
- Create: `app/Policies/CouponPolicy.php`
- Create: `app/Policies/BannerPolicy.php`

All policies follow the same pattern — only admins can create/update/delete. Any authenticated user can view.

---

### Task 2.10: Run Pint and Test Admin Routes

- [ ] **Step: Run pint, run existing tests, verify admin routes**

Run:
```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
php artisan route:list --path=admin
```

- [ ] **Step: Commit Phase 2**

```bash
git add .
git commit -m "feat: complete admin panel with CRUD for all entities"
```

---

## Phase 3: Public Storefront

### Task 3.1: Create Public Routes

**Files:**
- Modify: `routes/web.php`

```php
Route::view('/', 'welcome')->name('home');

// Product Listing
Route::livewire('/products', 'pages.products.index')->name('products.index');
Route::livewire('/category/{category:slug}', 'pages.products.index')->name('products.by-category');
Route::livewire('/brand/{brand:slug}', 'pages.products.index')->name('products.by-brand');

// Product Detail
Route::livewire('/product/{product:slug}', 'pages.products.show')->name('products.show');

// Cart
Route::livewire('/cart', 'pages.cart')->name('cart');

// Checkout (auth required)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/checkout', 'pages.checkout')->name('checkout');
    Route::livewire('/checkout/{order}', 'pages.checkout.payment')->name('checkout.payment');
});

// Customer Dashboard
Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('account', 'account/orders');
    Route::livewire('account/orders', 'pages.account.orders')->name('account.orders');
    Route::livewire('account/orders/{order}', 'pages.account.order-detail')->name('account.orders.show');
    Route::livewire('account/addresses', 'pages.account.addresses')->name('account.addresses');
    Route::livewire('account/reviews', 'pages.account.reviews')->name('account.reviews');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
```

---

### Task 3.2: Home Page

**Files:**
- Modify: `resources/views/welcome.blade.php`

Build the home page as a full Livewire page component (or Blade view fetching data via view composers). Sections:
1. **Hero banner slider**: banners with category "home" and position "hero", carousel/slider
2. **Featured products section**: products where is_featured and is_available
3. **New arrivals**: latest available products
4. **Featured categories**: categories where is_featured, with product count
5. **Featured brands**: brands where is_featured
6. **Sectional banners**: featured banners in different positions (home_top, home_middle, home_bottom)

Use Tailwind CSS grid/flex layouts. Use Alpine.js for the banner slider.

---

### Task 3.3: Product Listing Page with Filters

**Files:**
- Create: `resources/views/livewire/pages/products/index.blade.php`

Full page Livewire component. Features:
- Product grid with thumbnail, name, price, sale price badge
- Sidebar filters: category tree, brand list, attribute filters (size, color), price range slider
- Sorting: newest, price low-high, price high-low
- Search query input
- Pagination (12 per page)
- URL query string binding for shareable filtered URLs
- Accepts optional `$category` or `$brand` route parameters for filtered views

Use `#[Url]` attribute for filter state persistence. Use `withQueryString()` on paginator.

---

### Task 3.4: Product Detail Page

**Files:**
- Create: `resources/views/livewire/pages/products/show.blade.php`

Full page Livewire component showing:
- Image gallery (thumbnail + gallery from medialibrary) with Alpine.js lightbox
- Product name, description
- Variant selector (attribute-based: pick size, pick color → finds matching variant)
- Price display (effective price, original price with strikethrough if on sale)
- Stock status
- Quantity selector
- "Add to Cart" button
- "Buy Now" button (creates order directly)
- Reviews section with rating breakdown
- Related products (same category or brand)

---

### Task 3.5: Related Products Component

**Files:**
- Create: `resources/views/livewire/components/related-products.blade.php`

Inline SFC component. Shows products from same category or brand, limited to 8, excluding current product.

---

### Task 3.6: Commit Phase 3

```bash
git add .
git commit -m "feat: public storefront - home, product listing, product detail"
```

---

## Phase 4: Cart & Checkout

### Task 4.1: Create CartService

**Files:**
- Create: `app/Services/CartService.php`

Service class handling:
- `getOrCreateCart(User $user, ?string $sessionId): Cart`
- `addItem(Cart $cart, int $variantId, int $quantity, ?string $description): CartItem`
- `updateItemQuantity(Cart $cart, int $itemId, int $quantity): void`
- `removeItem(Cart $cart, int $itemId): void`
- `applyCoupon(Cart $cart, string $code): bool`
- `removeCoupon(Cart $cart): void`
- `clearCart(Cart $cart): void`
- `recalculateCart(Cart $cart): void`
- `getCartItemCount(User $user, ?string $sessionId): int`

All cart operations should call `recalculate()` after changes.

---

### Task 4.2: Create OrderService

**Files:**
- Create: `app/Services/OrderService.php`

Service class handling:
- `createOrderFromCart(Cart $cart, User $user, array $addressData): Order` — wraps in DB transaction, creates order with items, reserves stock, creates payment record
- `createDirectOrder(User $user, int $variantId, int $quantity, array $addressData): Order` — buy now without cart
- `processPayment(Order $order, string $method): bool` — simulate payment, update payment/order status
- `completeOrder(Order $order): void` — finalize stock (move from reserved to actual deduction)
- `cancelOrder(Order $order): void` — release reserved stock, update status

Critical: Use `lockForUpdate()` on stock rows and coupon rows to prevent race conditions. NEVER reduce stock directly — only reserve first.

---

### Task 4.3: Create CouponService

**Files:**
- Create: `app/Services/CouponService.php`

Service class handling:
- `validateCoupon(string $code, float $subtotal, int $userId): array` — returns validated coupon or error
- `calculateDiscount(Coupon $coupon, float $subtotal): float`
- `recordUsage(Coupon $coupon, User $user, Order $order): void` — increments used_count with lockForUpdate

---

### Task 4.4: Cart Page

**Files:**
- Create: `resources/views/livewire/pages/cart.blade.php`

Full page component with:
- Cart items list: product image, name, variant description, price, quantity input, line total, remove button
- Coupon code input with apply/remove button
- Cart summary: subtotal, discount, total
- "Proceed to Checkout" button (redirects to checkout, requires auth)
- "Continue Shopping" link
- Empty cart state

Uses CartService for all operations. Recalculates on every quantity change.

---

### Task 4.5: Cart Dropdown Component (Navbar)

**Files:**
- Create: `resources/views/livewire/components/cart-dropdown.blade.php`

Inline SFC showing:
- Cart item count badge
- Dropdown with cart items (name, quantity, price), subtotal, "View Cart" and "Checkout" links
- Uses `#[Computed]` for cart data

---

### Task 4.6: Checkout Page

**Files:**
- Create: `resources/views/livewire/pages/checkout.blade.php`
- Create: `resources/views/livewire/pages/checkout/payment.blade.php`

**Checkout**: Multi-step form:
1. Shipping address (select saved or add new)
2. Billing address (same as shipping or different)
3. Order summary review
4. Place order button → creates order via OrderService, redirects to payment

**Payment**: Simulated payment form:
- Select payment method (card, bkash, bank, cash)
- "Pay Now" button → calls OrderService::processPayment
- Success/failure state with redirect to order detail

---

### Task 4.7: Commit Phase 4

```bash
git add .
git commit -m "feat: cart, checkout, order placement with stock reservation"
```

---

## Phase 5: Customer Dashboard

### Task 5.1: Customer Orders Page

**Files:**
- Create: `resources/views/livewire/pages/account/orders.blade.php`

Table of user's orders: order number, date, status badge, total, "View" link. Filter by status.

---

### Task 5.2: Customer Order Detail Page

**Files:**
- Create: `resources/views/livewire/pages/account/order-detail.blade.php`

Detailed order view: items, shipping address, payment info, status timeline.

---

### Task 5.3: Customer Addresses Page

**Files:**
- Create: `resources/views/livewire/pages/account/addresses.blade.php`

Address management: list addresses, add/edit/delete. Toggle default. Separate billing/shipping.

---

### Task 5.4: Customer Reviews Page

**Files:**
- Create: `resources/views/livewire/pages/account/reviews.blade.php`

List user's reviews with product name, rating, comment. Edit/delete capability.

---

### Task 5.5: Product Review Component (on Product Detail)

**Files:**
- Create: `resources/views/livewire/components/review-form.blade.php`

Inline SFC on product detail page: rating (1-5 stars), comment textarea, submit button. Only for authenticated users who haven't already reviewed.

---

### Task 5.6: Commit Phase 5

```bash
git add .
git commit -m "feat: customer dashboard - orders, addresses, reviews"
```

---

## Phase 6: Navigation & Layout

### Task 6.1: Update Public Navbar

**Files:**
- Modify: `resources/views/layouts/app/header.blade.php`

Replace the starter kit navbar with ecommerce navbar:
- Logo (left)
- Search bar (center) — links to products page with query
- Featured categories dropdown
- Cart dropdown component (with badge)
- User menu: login/register (guest) or account dropdown (auth)

---

### Task 6.2: Create Footer

**Files:**
- Create: `resources/views/components/footer.blade.php`

Footer with:
- Featured categories links
- Featured brands links
- Quick links (about, contact, etc.)
- Copyright

---

### Task 6.3: Update Public Layout

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

Update to use the new navbar and include the footer. Remove sidebar layout for public pages.

---

### Task 6.4: Create Separate Layouts

**Files:**
- Modify: `resources/views/layouts/app.blade.php` — public storefront layout (header + footer)
- Keep: `resources/views/layouts/app/sidebar.blade.php` — admin/dashboard layout

The public layout has the ecommerce navbar and footer. The sidebar layout is for admin/dashboard.

---

### Task 6.5: Commit Phase 6

```bash
git add .
git commit -m "feat: navigation, footer, and layout updates"
```

---

## Phase 7: Testing

### Task 7.1: Admin Panel Tests

**Files:**
- Create: `tests/Feature/Admin/CategoryTest.php`
- Create: `tests/Feature/Admin/BrandTest.php`
- Create: `tests/Feature/Admin/ProductTest.php`
- Create: `tests/Feature/Admin/OrderTest.php`
- Create: `tests/Feature/Admin/CouponTest.php`
- Create: `tests/Feature/Admin/BannerTest.php`
- Create: `tests/Feature/Admin/UserTest.php`

Tests for each admin CRUD:
- Admin can view index
- Admin can create
- Admin can edit
- Admin can delete
- Non-admin gets 403
- Guest gets redirected

---

### Task 7.2: Public Storefront Tests

**Files:**
- Create: `tests/Feature/Public/HomePageTest.php`
- Create: `tests/Feature/Public/ProductListingTest.php`
- Create: `tests/Feature/Public/ProductDetailTest.php`

Tests:
- Home page loads with banners, featured products, new arrivals
- Product listing with filters (category, brand, search, sort)
- Product detail shows product info, variants, reviews

---

### Task 7.3: Cart Tests

**Files:**
- Create: `tests/Feature/CartTest.php`

Tests:
- Add item to cart
- Update item quantity
- Remove item from cart
- Apply valid coupon
- Apply expired coupon (should fail)
- Apply coupon exceeding usage limit (should fail)
- Remove coupon
- Cart calculations correct (subtotal, discount, total)

---

### Task 7.4: Checkout and Order Tests

**Files:**
- Create: `tests/Feature/CheckoutTest.php`
- Create: `tests/Feature/OrderTest.php`

Tests:
- Checkout page requires auth
- Place order from cart (creates order, reserves stock)
- Direct buy now order
- Payment simulation success → order status updated
- Payment simulation failure → order status updated
- Stock reservation works correctly
- Order cancellation releases stock
- Coupon usage limit enforced with concurrency (lockForUpdate)

---

### Task 7.5: Customer Dashboard Tests

**Files:**
- Create: `tests/Feature/Account/OrderHistoryTest.php`
- Create: `tests/Feature/Account/AddressTest.php`
- Create: `tests/Feature/Account/ReviewTest.php`

Tests for viewing, creating, updating, deleting within customer dashboard.

---

### Task 7.6: Service Unit Tests

**Files:**
- Create: `tests/Unit/Services/CartServiceTest.php`
- Create: `tests/Unit/Services/OrderServiceTest.php`
- Create: `tests/Unit/Services/CouponServiceTest.php`

Unit tests for business logic: discount calculations, stock reservation, coupon validation, cart math.

---

### Task 7.7: Run All Tests and Final Pint

- [ ] **Step 1: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 2: Run full test suite**

Run: `php artisan test --compact`

- [ ] **Step 3: Fix any failures**

- [ ] **Step 4: Final commit**

```bash
git add .
git commit -m "feat: comprehensive test suite for all features"
```

---

## Self-Review Checklist

- [x] **Spec coverage**: All CLAUDE.md requirements mapped to tasks (20+ tables, all relationships, admin CRUD, public storefront, cart/checkout, customer dashboard, tests)
- [x] **Placeholder scan**: No TBD/TODO placeholders — all tasks have concrete code or clear instructions
- [x] **Type consistency**: Model names, method names, and relationships consistent across all tasks
- [x] **Missing items check**:
  - Media library configured on Product and Banner models ✓
  - Sluggable on Category, Brand, Product, Banner ✓
  - Adjacency list on Category ✓
  - Userstamps on relevant models ✓
  - Stock reservation flow (reserve first, finalize after payment) ✓
  - Coupon locking for concurrency ✓
  - Order number auto-generation ✓
  - Effective price / isOnSale accessors ✓
  - All relationships defined ✓
  - Admin middleware ✓
  - Customer dashboard routes ✓
