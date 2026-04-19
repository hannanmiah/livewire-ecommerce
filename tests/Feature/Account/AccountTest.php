<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Livewire\Livewire;

test('account orders page requires authentication', function () {
    $this->get(route('account.orders'))->assertRedirect(route('login'));
});

test('account order detail page requires authentication', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $this->get(route('account.orders.show', $order))->assertRedirect(route('login'));
});

test('account addresses page requires authentication', function () {
    $this->get(route('account.addresses'))->assertRedirect(route('login'));
});

test('account reviews page requires authentication', function () {
    $this->get(route('account.reviews'))->assertRedirect(route('login'));
});

test('user can view order history', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'order_number' => 'ORD-TEST123']);

    Livewire::actingAs($user)
        ->test('pages.account.orders')
        ->assertOk()
        ->assertSee('ORD-TEST123');
});

test('user can filter orders by status', function () {
    $user = User::factory()->create();
    $pending = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'order_number' => 'ORD-PEND01']);
    $paid = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid', 'order_number' => 'ORD-PAID01']);

    Livewire::actingAs($user)
        ->test('pages.account.orders')
        ->set('filter_status', 'pending')
        ->assertSee('ORD-PEND01')
        ->assertDontSee('ORD-PAID01');
});

test('user only sees their own orders', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    Order::factory()->create(['user_id' => $user1->id, 'order_number' => 'ORD-USER1']);
    Order::factory()->create(['user_id' => $user2->id, 'order_number' => 'ORD-USER2']);

    Livewire::actingAs($user1)
        ->test('pages.account.orders')
        ->assertSee('ORD-USER1')
        ->assertDontSee('ORD-USER2');
});

test('user can view order detail', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Ordered Product']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 25.00]);
    $order = Order::factory()->create(['user_id' => $user->id]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'variant_id' => $variant->id,
        'price' => 25.00,
        'quantity' => 2,
        'total' => 50.00,
    ]);

    $this->actingAs($user)
        ->get(route('account.orders.show', $order))
        ->assertOk()
        ->assertSee($order->order_number);
});

test('user can manage addresses page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages.account.addresses')
        ->assertOk()
        ->assertSee('My Addresses');
});

test('user can add a new address', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages.account.addresses')
        ->call('addAddress')
        ->set('address_line', '123 Test Street')
        ->set('city', 'Test City')
        ->set('postal_code', '12345')
        ->set('country', 'Test Country')
        ->set('type', 'shipping')
        ->call('saveAddress');

    expect($user->addresses()->count())->toBe(1);
    $address = $user->addresses->first();
    expect($address->address_line)->toBe('123 Test Street');
    expect($address->city)->toBe('Test City');
    expect($address->type)->toBe('shipping');
});

test('user can edit an existing address', function () {
    $user = User::factory()->create();
    $address = $user->addresses()->create([
        'type' => 'shipping',
        'address_line' => 'Old Address',
        'city' => 'Old City',
        'postal_code' => '00000',
        'country' => 'Old Country',
        'is_default' => false,
    ]);

    Livewire::actingAs($user)
        ->test('pages.account.addresses')
        ->call('editAddress', $address->id)
        ->set('address_line', 'New Address')
        ->set('city', 'New City')
        ->set('country', 'New Country')
        ->call('saveAddress');

    expect($address->fresh()->address_line)->toBe('New Address');
    expect($address->fresh()->city)->toBe('New City');
});

test('user can delete an address', function () {
    $user = User::factory()->create();
    $address = $user->addresses()->create([
        'type' => 'shipping',
        'address_line' => 'To Delete',
        'city' => 'City',
        'country' => 'Country',
    ]);

    Livewire::actingAs($user)
        ->test('pages.account.addresses')
        ->call('deleteAddress', $address->id);

    expect($user->addresses()->count())->toBe(0);
});

test('user can set default address', function () {
    $user = User::factory()->create();
    $addr1 = $user->addresses()->create([
        'type' => 'shipping',
        'address_line' => 'Address 1',
        'city' => 'City',
        'country' => 'Country',
        'is_default' => true,
    ]);
    $addr2 = $user->addresses()->create([
        'type' => 'shipping',
        'address_line' => 'Address 2',
        'city' => 'City',
        'country' => 'Country',
        'is_default' => false,
    ]);

    Livewire::actingAs($user)
        ->test('pages.account.addresses')
        ->call('setDefault', $addr2->id);

    expect($addr1->fresh()->is_default)->toBeFalse();
    expect($addr2->fresh()->is_default)->toBeTrue();
});

test('user can view reviews page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages.account.reviews')
        ->assertOk()
        ->assertSee('My Reviews');
});

test('user sees their reviews on reviews page', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Reviewed Product']);
    Review::factory()->create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'rating' => 4,
        'comment' => 'My review text',
    ]);

    Livewire::actingAs($user)
        ->test('pages.account.reviews')
        ->assertSee('Reviewed Product')
        ->assertSee('My review text');
});

test('user can edit their review', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $review = Review::factory()->create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'rating' => 3,
        'comment' => 'Old comment',
    ]);

    Livewire::actingAs($user)
        ->test('pages.account.reviews')
        ->call('editReview', $review->id)
        ->set('edit_rating', 5)
        ->set('edit_comment', 'Updated comment')
        ->call('updateReview');

    expect($review->fresh()->rating)->toBe(5);
    expect($review->fresh()->comment)->toBe('Updated comment');
});

test('user can delete their review', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $review = Review::factory()->create([
        'product_id' => $product->id,
        'user_id' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test('pages.account.reviews')
        ->call('deleteReview', $review->id);

    expect(Review::find($review->id))->toBeNull();
});

test('user only sees their own reviews', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $product = Product::factory()->create(['name' => 'Shared Product']);
    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user1->id, 'comment' => 'User1 review']);
    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user2->id, 'comment' => 'User2 review']);

    Livewire::actingAs($user1)
        ->test('pages.account.reviews')
        ->assertSee('User1 review')
        ->assertDontSee('User2 review');
});
