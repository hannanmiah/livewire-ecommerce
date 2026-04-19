<?php

use App\Models\Product;

test('product thumbnail accessor returns null when thumbnail media does not exist', function () {
    $product = Product::factory()->create();

    expect($product->thumbnail)->toBeNull();
});
