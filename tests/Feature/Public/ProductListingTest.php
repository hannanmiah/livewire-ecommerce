<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use Livewire\Livewire;

test('products page loads', function () {
    $this->get(route('products.index'))->assertOk();
});

test('products page shows available products', function () {
    $product = Product::factory()->create(['name' => 'Visible Product']);
    ProductVariant::factory()->create(['product_id' => $product->id]);
    Stock::factory()->inStock()->create(['variant_id' => $product->variants->first()->id]);

    Livewire::test('pages.products.index')
        ->assertSee('Visible Product');
});

test('products page does not show unavailable products', function () {
    $product = Product::factory()->unavailable()->create(['name' => 'Unavailable Product']);

    Livewire::test('pages.products.index')
        ->assertDontSee('Unavailable Product');
});

test('products page can filter by category route', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Cat Product']);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get(route('products.by-category', $category))->assertSee('Cat Product');
});

test('products page can filter by brand route', function () {
    $brand = Brand::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id, 'name' => 'Brand Product']);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get(route('products.by-brand', $brand))->assertSee('Brand Product');
});

test('products page can search by name', function () {
    $product1 = Product::factory()->create(['name' => 'Special Item XYZ']);
    ProductVariant::factory()->create(['product_id' => $product1->id]);

    $product2 = Product::factory()->create(['name' => 'Other Item ABC']);
    ProductVariant::factory()->create(['product_id' => $product2->id]);

    Livewire::test('pages.products.index')
        ->set('search', 'Special')
        ->assertSee('Special Item XYZ')
        ->assertDontSee('Other Item ABC');
});

test('products page can filter by selected brands', function () {
    $brand1 = Brand::factory()->create();
    $brand2 = Brand::factory()->create();
    $product1 = Product::factory()->create(['brand_id' => $brand1->id, 'name' => 'Brand One Product']);
    ProductVariant::factory()->create(['product_id' => $product1->id]);
    $product2 = Product::factory()->create(['brand_id' => $brand2->id, 'name' => 'Brand Two Product']);
    ProductVariant::factory()->create(['product_id' => $product2->id]);

    Livewire::test('pages.products.index')
        ->set('selected_brands', [(string) $brand1->id])
        ->assertSee('Brand One Product')
        ->assertDontSee('Brand Two Product');
});

test('products page can filter by selected categories', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $product1 = Product::factory()->create(['category_id' => $category1->id, 'name' => 'Cat One Product']);
    ProductVariant::factory()->create(['product_id' => $product1->id]);
    $product2 = Product::factory()->create(['category_id' => $category2->id, 'name' => 'Cat Two Product']);
    ProductVariant::factory()->create(['product_id' => $product2->id]);

    Livewire::test('pages.products.index')
        ->set('selected_categories', [(string) $category1->id])
        ->assertSee('Cat One Product')
        ->assertDontSee('Cat Two Product');
});

test('products page can sort by newest', function () {
    $old = Product::factory()->create(['name' => 'Old Product']);
    ProductVariant::factory()->create(['product_id' => $old->id]);
    $new = Product::factory()->create(['name' => 'New Product']);
    ProductVariant::factory()->create(['product_id' => $new->id]);

    Livewire::test('pages.products.index')
        ->set('sort', 'newest')
        ->assertOk();
});

test('products page can clear filters', function () {
    $brand = Brand::factory()->create();
    Product::factory()->create(['brand_id' => $brand->id, 'name' => 'Filtered']);
    Product::factory()->create(['name' => 'Unfiltered']);

    Livewire::test('pages.products.index')
        ->set('search', 'Filtered')
        ->set('selected_brands', [(string) $brand->id])
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('selected_brands', []);
});

test('products page shows empty state when no products match', function () {
    Livewire::test('pages.products.index')
        ->set('search', 'nonexistent-product-xyz-123')
        ->assertSee('No products found');
});
