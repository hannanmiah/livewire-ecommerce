<?php

use App\Models\User;
use Livewire\Livewire;

test('admin can view users index', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin::users.index'))
        ->assertSuccessful()
        ->assertSee('Users');
});

test('admin can view user detail page', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin::users.show', $user))
        ->assertSuccessful()
        ->assertSee('User Details');
});

test('admin can update user role to editor', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['role' => 'customer']);

    Livewire::actingAs($admin)
        ->test('admin::users.show', ['user' => $user])
        ->set('role', 'editor')
        ->call('updateRole');

    expect($user->fresh())->role->toBe('editor');
});

test('admin can update user role to admin', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['role' => 'customer']);

    Livewire::actingAs($admin)
        ->test('admin::users.show', ['user' => $user])
        ->set('role', 'admin')
        ->call('updateRole');

    expect($user->fresh())->role->toBe('admin');
});

test('admin can update user role back to customer', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['role' => 'editor']);

    Livewire::actingAs($admin)
        ->test('admin::users.show', ['user' => $user])
        ->set('role', 'customer')
        ->call('updateRole');

    expect($user->fresh())->role->toBe('customer');
});

test('user role validation rejects invalid values', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['role' => 'customer']);

    Livewire::actingAs($admin)
        ->test('admin::users.show', ['user' => $user])
        ->set('role', 'super_admin')
        ->call('updateRole')
        ->assertHasErrors(['role' => 'in']);
});

test('users index can search by name', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $component = Livewire::actingAs($admin)
        ->test('admin::users.index')
        ->set('search', 'John');

    $component->assertSee('John Doe');
    $component->assertDontSee('Jane Smith');
});

test('users index can search by email', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'john@example.com']);
    User::factory()->create(['email' => 'jane@example.com']);

    $component = Livewire::actingAs($admin)
        ->test('admin::users.index')
        ->set('search', 'john@example');

    $component->assertSee('john@example.com');
    $component->assertDontSee('jane@example.com');
});

test('users index can filter by role', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['role' => 'customer', 'name' => 'Customer User']);
    User::factory()->create(['role' => 'editor', 'name' => 'Editor User']);

    $component = Livewire::actingAs($admin)
        ->test('admin::users.index')
        ->set('filter_role', 'customer');

    $component->assertSee('Customer User');
    $component->assertDontSee('Editor User');
});

test('user detail page shows user information', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

    $this->actingAs($admin)
        ->get(route('admin::users.show', $user))
        ->assertSuccessful()
        ->assertSee('Test User')
        ->assertSee('test@example.com');
});
