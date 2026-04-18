<?php

use App\Models\Order;
use App\Models\User;
use Livewire\Livewire;

test('admin can view orders index', function () {
    $admin = User::factory()->admin()->create();
    Order::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertSuccessful()
        ->assertSee('Orders');
});

test('admin can view order detail page', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertSuccessful()
        ->assertSee($order->order_number);
});

test('admin can update order status', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'paid')
        ->call('updateStatus');

    expect($order->fresh())->status->toBe('paid');
});

test('admin can update shipping status', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['shipping_status' => 'pending']);

    Livewire::actingAs($admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('shipping_status', 'shipped')
        ->call('updateShippingStatus');

    expect($order->fresh())->shipping_status->toBe('shipped');
});

test('admin can update order status to completed', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'completed')
        ->call('updateStatus');

    expect($order->fresh())->status->toBe('completed');
});

test('admin can update order status to cancelled', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'cancelled')
        ->call('updateStatus');

    expect($order->fresh())->status->toBe('cancelled');
});

test('admin can update shipping status to delivered', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['shipping_status' => 'pending']);

    Livewire::actingAs($admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('shipping_status', 'delivered')
        ->call('updateShippingStatus');

    expect($order->fresh())->shipping_status->toBe('delivered');
});

test('order status validation rejects invalid values', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => 'pending']);

    Livewire::actingAs($admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('status', 'invalid_status')
        ->call('updateStatus')
        ->assertHasErrors(['status' => 'in']);
});

test('shipping status validation rejects invalid values', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['shipping_status' => 'pending']);

    Livewire::actingAs($admin)
        ->test('admin.orders.show', ['order' => $order])
        ->set('shipping_status', 'invalid_status')
        ->call('updateShippingStatus')
        ->assertHasErrors(['shipping_status' => 'in']);
});

test('orders index can search by order number', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['order_number' => 'ORD-TEST12345']);
    Order::factory()->create(['order_number' => 'ORD-OTHER99999']);

    $component = Livewire::actingAs($admin)
        ->test('admin.orders.index')
        ->set('search', 'TEST12345');

    $component->assertSee('ORD-TEST12345');
    $component->assertDontSee('ORD-OTHER99999');
});

test('orders index can filter by status', function () {
    $admin = User::factory()->admin()->create();
    Order::factory()->create(['status' => 'pending']);
    Order::factory()->create(['status' => 'completed']);

    $component = Livewire::actingAs($admin)
        ->test('admin.orders.index')
        ->set('filter_status', 'pending');

    $component->assertSee('Pending');
});

test('orders index can filter by payment status', function () {
    $admin = User::factory()->admin()->create();
    Order::factory()->create(['payment_status' => 'unpaid']);
    Order::factory()->create(['payment_status' => 'paid']);

    $component = Livewire::actingAs($admin)
        ->test('admin.orders.index')
        ->set('filter_payment_status', 'paid');

    $component->assertSee('Paid');
});
