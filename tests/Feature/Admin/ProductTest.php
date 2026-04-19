<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use Livewire\Livewire;

test('admin can view products index', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();
    Product::factory()->count(3)->create(['brand_id' => $brand->id]);

    $this->actingAs($admin)
        ->get(route('admin::products.index'))
        ->assertSuccessful()
        ->assertSee('Products');
});

test('admin can view create product page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin::products.create'))
        ->assertSuccessful()
        ->assertSee('Create Product');
});

test('admin can create product with variant', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin::products.create')
        ->set('name', 'Test Product')
        ->set('description', 'A test product description')
        ->set('category_id', $category->id)
        ->set('brand_id', $brand->id)
        ->set('variants.0.sku', 'SKU-TEST-001')
        ->set('variants.0.price', 29.99)
        ->set('variants.0.stock_quantity', 10)
        ->call('save')
        ->assertRedirect(route('admin::products.index'));

    $product = Product::first();
    expect($product)->name->toBe('Test Product');
    expect($product)->description->toBe('A test product description');
    expect($product)->category_id->toBe($category->id);
    expect($product)->brand_id->toBe($brand->id);

    $variant = ProductVariant::first();
    expect($variant)->sku->toBe('SKU-TEST-001');
    expect((float) $variant->price)->toBe(29.99);

    $stock = Stock::first();
    expect($stock)->quantity->toBe(10);
});

test('admin can create a featured product', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin::products.create')
        ->set('name', 'Featured Product')
        ->set('brand_id', $brand->id)
        ->set('is_featured', true)
        ->set('variants.0.sku', 'SKU-FEAT-001')
        ->set('variants.0.price', 49.99)
        ->set('variants.0.stock_quantity', 5)
        ->call('save');

    expect(Product::first())->is_featured->toBeTrue();
});

test('product creation requires a name', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin::products.create')
        ->set('name', '')
        ->set('brand_id', $brand->id)
        ->set('variants.0.sku', 'SKU-TEST')
        ->set('variants.0.price', 10)
        ->set('variants.0.stock_quantity', 1)
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('product creation requires a brand', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin::products.create')
        ->set('name', 'Test Product')
        ->set('brand_id', null)
        ->set('variants.0.sku', 'SKU-TEST')
        ->set('variants.0.price', 10)
        ->set('variants.0.stock_quantity', 1)
        ->call('save')
        ->assertHasErrors(['brand_id' => 'required']);
});

test('product creation requires at least one variant', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin::products.create')
        ->set('name', 'Test Product')
        ->set('brand_id', $brand->id)
        ->set('variants', [])
        ->call('save')
        ->assertHasErrors(['variants' => 'required']);
});

test('product variant requires a unique sku', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();
    ProductVariant::factory()->create(['sku' => 'SKU-DUPLICATE']);

    Livewire::actingAs($admin)
        ->test('admin::products.create')
        ->set('name', 'Test Product')
        ->set('brand_id', $brand->id)
        ->set('variants.0.sku', 'SKU-DUPLICATE')
        ->set('variants.0.price', 10)
        ->set('variants.0.stock_quantity', 1)
        ->call('save')
        ->assertHasErrors(['variants.0.sku' => 'unique']);
});

test('admin can view edit product page', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs($admin)
        ->get(route('admin::products.edit', $product))
        ->assertSuccessful()
        ->assertSee('Edit Product');
});

test('admin can update a product', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => 'Old Name']);
    $brand = Brand::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
    Stock::factory()->create(['variant_id' => $variant->id]);

    Livewire::actingAs($admin)
        ->test('admin::products.edit', ['product' => $product])
        ->set('name', 'Updated Name')
        ->set('brand_id', $brand->id)
        ->set('variants.0.sku', $variant->sku)
        ->set('variants.0.price', 39.99)
        ->set('variants.0.stock_quantity', 20)
        ->call('save')
        ->assertRedirect(route('admin::products.index'));

    expect($product->fresh())->name->toBe('Updated Name');
});

test('admin can delete a product from edit page', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    Livewire::actingAs($admin)
        ->test('admin::products.edit', ['product' => $product])
        ->call('delete')
        ->assertRedirect(route('admin::products.index'));

    expect(Product::find($product->id))->toBeNull();
});

test('admin can delete a product from index page', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin::products.index')
        ->call('delete', $product->id);

    expect(Product::find($product->id))->toBeNull();
});

test('products index can search by name', function () {
    $admin = User::factory()->admin()->create();
    Product::factory()->create(['name' => 'Laptop Pro']);
    Product::factory()->create(['name' => 'Running Shoes']);

    $component = Livewire::actingAs($admin)
        ->test('admin::products.index')
        ->set('search', 'Laptop');

    $component->assertSee('Laptop Pro');
    $component->assertDontSee('Running Shoes');
});

test('products index can filter by category', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Electronics']);
    Product::factory()->create(['name' => 'Laptop', 'category_id' => $category->id]);
    Product::factory()->create(['name' => 'Shoes']);

    $component = Livewire::actingAs($admin)
        ->test('admin::products.index')
        ->set('filter_category', $category->id);

    $component->assertSee('Laptop');
    $component->assertDontSee('Shoes');
});

test('products index can filter by brand', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create(['name' => 'Nike']);
    Product::factory()->create(['name' => 'Nike Shoe', 'brand_id' => $brand->id]);
    Product::factory()->create(['name' => 'Adidas Shoe']);

    $component = Livewire::actingAs($admin)
        ->test('admin::products.index')
        ->set('filter_brand', $brand->id);

    $component->assertSee('Nike Shoe');
    $component->assertDontSee('Adidas Shoe');
});

test('admin can add additional variant to product', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
    Stock::factory()->create(['variant_id' => $variant->id]);

    Livewire::actingAs($admin)
        ->test('admin::products.edit', ['product' => $product])
        ->call('addVariant')
        ->set('variants.1.sku', 'SKU-NEW-001')
        ->set('variants.1.price', 15.99)
        ->set('variants.1.stock_quantity', 5)
        ->call('save')
        ->assertRedirect(route('admin::products.index'));

    expect($product->fresh()->variants)->toHaveCount(2);
});

test('admin can remove a variant from product when more than one exists', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id]);
    $variant1 = ProductVariant::factory()->create(['product_id' => $product->id]);
    $variant2 = ProductVariant::factory()->create(['product_id' => $product->id]);
    Stock::factory()->create(['variant_id' => $variant1->id]);
    Stock::factory()->create(['variant_id' => $variant2->id]);

    Livewire::actingAs($admin)
        ->test('admin::products.edit', ['product' => $product])
        ->call('removeVariant', 0)
        ->call('save')
        ->assertRedirect(route('admin::products.index'));

    expect($product->fresh()->variants)->toHaveCount(1);
});
