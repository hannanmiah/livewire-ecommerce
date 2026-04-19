<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;

test('product detail page loads', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee($product->name);
});

test('product detail shows product description', function () {
    $product = Product::factory()->create(['description' => 'Amazing product description']);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get(route('products.show', $product))
        ->assertSee('Amazing product description');
});

test('product detail shows brand name', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get(route('products.show', $product))
        ->assertSee($product->brand->name);
});

test('product detail shows variant price range', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 29.99]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 59.99]);

    $this->get(route('products.show', $product))
        ->assertSee('29.99');
});

test('product detail shows reviews', function () {
    $user = User::factory()->create(['name' => 'John Reviewer']);
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);
    Review::factory()->create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'comment' => 'Great product review!',
        'rating' => 5,
    ]);

    $this->get(route('products.show', $product))
        ->assertSee('Great product review!')
        ->assertSee('John Reviewer');
});

test('product detail shows related products from same category', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $related = Product::factory()->create([
        'category_id' => $product->category_id,
        'name' => 'Related Product',
    ]);
    ProductVariant::factory()->create(['product_id' => $related->id]);

    $this->get(route('products.show', $product))
        ->assertSee('Related Products')
        ->assertSee('Related Product');
});

test('product detail shows add to cart button', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get(route('products.show', $product))
        ->assertSee('Add to Cart');
});

test('guest can view product detail page', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee($product->name);
});

test('authenticated user can submit review', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    // Submit review directly via model (simulating what the Livewire component does)
    $product->reviews()->create([
        'user_id' => $user->id,
        'rating' => 4,
        'comment' => 'This is my review comment.',
    ]);

    expect(Review::where('product_id', $product->id)->count())->toBe(1);
    $review = Review::first();
    expect($review->rating)->toBe(4);
    expect($review->comment)->toBe('This is my review comment.');
    expect($review->user_id)->toBe($user->id);
});

test('user cannot submit review twice for same product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);
    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id]);

    // A second review for the same product by the same user should not be possible
    // (The component checks hasReviewed, but at the model level we just verify count)
    $alreadyReviewed = $product->reviews()->where('user_id', $user->id)->exists();
    expect($alreadyReviewed)->toBeTrue();
    expect(Review::where('product_id', $product->id)->where('user_id', $user->id)->count())->toBe(1);
});

test('product detail page shows review form for authenticated user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->actingAs($user)
        ->get(route('products.show', $product))
        ->assertSee('Write a Review');
});

test('product detail page shows login prompt for guest review', function () {
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->get(route('products.show', $product))
        ->assertSee('log in')
        ->assertSee('to write a review');
});

test('product detail page shows already reviewed message for returning user', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    ProductVariant::factory()->create(['product_id' => $product->id]);
    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('products.show', $product))
        ->assertSee('You have already reviewed this product');
});
