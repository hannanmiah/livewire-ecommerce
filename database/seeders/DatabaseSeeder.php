<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Test customer
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 10 additional users
        User::factory(10)->create();

        // Categories (3 featured, some with children)
        $electronics = Category::factory()->featured()->create(['name' => 'Electronics']);
        $clothing = Category::factory()->featured()->create(['name' => 'Clothing']);
        $home = Category::factory()->featured()->create(['name' => 'Home & Garden']);
        Category::factory()->create(['name' => 'Phones', 'parent_id' => $electronics->id]);
        Category::factory()->create(['name' => 'Laptops', 'parent_id' => $electronics->id]);
        Category::factory(5)->create();

        // Brands (2 featured)
        Brand::factory()->featured()->create(['name' => 'TechPro']);
        Brand::factory()->featured()->create(['name' => 'StyleCo']);
        Brand::factory(5)->create();

        // Attributes with values
        $sizeAttr = Attribute::factory()->create(['name' => 'Size']);
        $colorAttr = Attribute::factory()->create(['name' => 'Color']);
        $materialAttr = Attribute::factory()->create(['name' => 'Material']);

        foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size) {
            AttributeValue::factory()->create(['attribute_id' => $sizeAttr->id, 'value' => $size]);
        }
        foreach (['Red', 'Blue', 'Green', 'Black', 'White'] as $color) {
            AttributeValue::factory()->create(['attribute_id' => $colorAttr->id, 'value' => $color]);
        }
        foreach (['Cotton', 'Polyester', 'Wool', 'Leather'] as $material) {
            AttributeValue::factory()->create(['attribute_id' => $materialAttr->id, 'value' => $material]);
        }

        // Products (50 total, 10 featured, all available)
        Product::factory(10)->featured()->create();
        Product::factory(40)->create();

        // For each product, create variants with stock
        Product::all()->each(function ($product) use ($sizeAttr, $colorAttr) {
            $sizes = $sizeAttr->values->random(min(2, $sizeAttr->values->count()));
            $colors = $colorAttr->values->random(min(2, $colorAttr->values->count()));

            foreach ($sizes as $size) {
                foreach ($colors as $color) {
                    $variant = ProductVariant::factory()->create([
                        'product_id' => $product->id,
                        'sku' => "SKU-{$product->id}-{$size->value}-{$color->value}",
                    ]);
                    $variant->attributeValues()->attach([$size->id, $color->id]);
                    Stock::factory()->inStock()->create(['variant_id' => $variant->id]);
                }
            }
        });

        // Banners
        Banner::factory()->count(3)->featured()->create(['category' => 'home', 'position' => 'hero']);
        Banner::factory()->featured()->create(['category' => 'home', 'position' => 'home_top']);
        Banner::factory()->featured()->create(['category' => 'home', 'position' => 'home_middle']);
        Banner::factory()->featured()->create(['category' => 'home', 'position' => 'home_bottom']);

        // Coupons
        Coupon::factory()->create(['code' => 'SAVE10', 'type' => 'fixed', 'value' => 10.00, 'expires_at' => now()->addMonth()]);
        Coupon::factory()->percent()->create(['code' => 'PERCENT20', 'value' => 20.00, 'expires_at' => now()->addMonth()]);
    }
}
