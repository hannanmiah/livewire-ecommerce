<?php

use App\Models\User;

test('guests are redirected from admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('non-admin users get 403 on admin dashboard', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin users can access admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful();
});

test('guests are redirected from admin categories', function () {
    $this->get(route('admin.categories.index'))
        ->assertRedirect(route('login'));
});

test('non-admin users get 403 on admin categories', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});

test('guests are redirected from admin products', function () {
    $this->get(route('admin.products.index'))
        ->assertRedirect(route('login'));
});

test('non-admin users get 403 on admin products', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->get(route('admin.products.index'))
        ->assertForbidden();
});

test('guests are redirected from admin orders', function () {
    $this->get(route('admin.orders.index'))
        ->assertRedirect(route('login'));
});

test('non-admin users get 403 on admin orders', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->get(route('admin.orders.index'))
        ->assertForbidden();
});
