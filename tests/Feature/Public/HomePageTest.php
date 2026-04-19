<?php

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;

test('home page loads successfully', function () {
    $this->get(route('home'))->assertOk();
});

test('home page shows featured categories', function () {
    Category::factory()->featured()->create(['name' => 'Featured Category']);

    $this->get(route('home'))->assertSee('Featured Category');
});

test('home page does not show non-featured categories in featured section', function () {
    Category::factory()->create(['name' => 'Regular Category', 'featured_at' => null]);

    $this->get(route('home'))->assertDontSee('Regular Category');
});

test('home page shows featured products', function () {
    $product = Product::factory()->featured()->create(['name' => 'Featured Product']);
    ProductVariant::factory()->create(['product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $product->variants->first()->id]);

    $this->get(route('home'))->assertSee('Featured Product');
});

test('home page shows new arrivals', function () {
    $product = Product::factory()->create(['name' => 'New Arrival']);
    ProductVariant::factory()->create(['product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $product->variants->first()->id]);

    $this->get(route('home'))->assertSee('New Arrival');
});

test('home page shows featured brands', function () {
    Brand::factory()->featured()->create(['name' => 'Featured Brand']);

    $this->get(route('home'))->assertSee('Featured Brand');
});

test('home page does not show non-featured brands in featured section', function () {
    Brand::factory()->create(['name' => 'Regular Brand', 'featured_at' => null]);

    $this->get(route('home'))->assertDontSee('Regular Brand');
});

test('home page shows hero banners', function () {
    Banner::factory()->featured()->create([
        'category' => 'home',
        'position' => 'hero',
        'title' => 'Hero Banner',
    ]);

    $this->get(route('home'))->assertSee('Hero Banner');
});

test('home page shows sectional banners', function () {
    Banner::factory()->featured()->create([
        'category' => 'home',
        'position' => 'home_top',
        'title' => 'Top Section Banner',
    ]);

    $this->get(route('home'))->assertSee('Top Section Banner');
});

test('home page does not show non-featured banners', function () {
    Banner::factory()->create([
        'category' => 'home',
        'position' => 'hero',
        'title' => 'Draft Banner',
        'featured_at' => null,
    ]);

    $this->get(route('home'))->assertDontSee('Draft Banner');
});
