<?php

use App\Models\Coupon;
use App\Models\User;
use Livewire\Livewire;

test('admin can view coupons index', function () {
    $admin = User::factory()->admin()->create();
    Coupon::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.coupons.index'))
        ->assertSuccessful()
        ->assertSee('Coupons');
});

test('admin can view create coupon page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.coupons.create'))
        ->assertSuccessful()
        ->assertSee('Create Coupon');
});

test('admin can create a fixed coupon', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin::coupons.create')
        ->set('code', 'SAVE10')
        ->set('type', 'fixed')
        ->set('value', 10)
        ->call('save')
        ->assertRedirect(route('admin.coupons.edit', Coupon::first()));

    $coupon = Coupon::first();
    expect($coupon)->code->toBe('SAVE10');
    expect($coupon)->type->toBe('fixed');
    expect((float) $coupon->value)->toBe(10.0);
});

test('admin can create a percent coupon', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin::coupons.create')
        ->set('code', 'PERCENT20')
        ->set('type', 'percent')
        ->set('value', 20)
        ->call('save');

    $coupon = Coupon::first();
    expect($coupon)->type->toBe('percent');
    expect((float) $coupon->value)->toBe(20.0);
});

test('coupon creation uppercases the code', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin::coupons.create')
        ->set('code', 'lowercase')
        ->set('type', 'fixed')
        ->set('value', 5)
        ->call('save');

    expect(Coupon::first())->code->toBe('LOWERCASE');
});

test('admin can create coupon with all options', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin::coupons.create')
        ->set('code', 'FULLCOUPON')
        ->set('type', 'fixed')
        ->set('value', 15)
        ->set('min_order_amount', 50)
        ->set('usage_limit', 100)
        ->set('expires_at', now()->addMonth()->format('Y-m-d\TH:i'))
        ->call('save');

    $coupon = Coupon::first();
    expect($coupon)->code->toBe('FULLCOUPON');
    expect((float) $coupon->min_order_amount)->toBe(50.0);
    expect($coupon)->usage_limit->toBe(100);
    expect($coupon)->expires_at->not->toBeNull();
});

test('coupon creation requires a code', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin::coupons.create')
        ->set('code', '')
        ->set('type', 'fixed')
        ->set('value', 10)
        ->call('save')
        ->assertHasErrors(['code' => 'required']);
});

test('coupon creation requires a unique code', function () {
    $admin = User::factory()->admin()->create();
    Coupon::factory()->create(['code' => 'EXISTS']);

    Livewire::actingAs($admin)
        ->test('admin::coupons.create')
        ->set('code', 'EXISTS')
        ->set('type', 'fixed')
        ->set('value', 10)
        ->call('save')
        ->assertHasErrors(['code' => 'unique']);
});

test('coupon creation enforces unique code case-insensitively', function () {
    $admin = User::factory()->admin()->create();
    Coupon::factory()->create(['code' => 'SAVE10']);

    Livewire::actingAs($admin)
        ->test('admin::coupons.create')
        ->set('code', 'save10')
        ->set('type', 'fixed')
        ->set('value', 10)
        ->call('save')
        ->assertHasErrors(['code' => 'unique']);
});

test('coupon creation requires a valid type', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin::coupons.create')
        ->set('code', 'TEST')
        ->set('type', 'invalid')
        ->set('value', 10)
        ->call('save')
        ->assertHasErrors(['type' => 'in']);
});

test('coupon creation requires a value', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin::coupons.create')
        ->set('code', 'TEST')
        ->set('type', 'fixed')
        ->set('value', '')
        ->call('save')
        ->assertHasErrors(['value' => 'required']);
});

test('coupon creation prevents percent value above 100', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin::coupons.create')
        ->set('code', 'PERCENT150')
        ->set('type', 'percent')
        ->set('value', 150)
        ->call('save')
        ->assertHasErrors(['value' => 'max']);
});

test('admin can view edit coupon page', function () {
    $admin = User::factory()->admin()->create();
    $coupon = Coupon::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.coupons.edit', $coupon))
        ->assertSuccessful()
        ->assertSee('Edit Coupon');
});

test('admin can update a coupon', function () {
    $admin = User::factory()->admin()->create();
    $coupon = Coupon::factory()->create(['code' => 'OLD', 'value' => 5]);

    Livewire::actingAs($admin)
        ->test('admin::coupons.edit', ['coupon' => $coupon])
        ->set('code', 'UPDATED')
        ->set('value', 15)
        ->call('save');

    expect($coupon->fresh())->code->toBe('UPDATED');
    expect((float) $coupon->fresh()->value)->toBe(15.0);
});

test('coupon update enforces unique code case-insensitively', function () {
    $admin = User::factory()->admin()->create();
    Coupon::factory()->create(['code' => 'SAVE10']);
    $coupon = Coupon::factory()->create(['code' => 'OTHER10']);

    Livewire::actingAs($admin)
        ->test('admin::coupons.edit', ['coupon' => $coupon])
        ->set('code', 'save10')
        ->set('type', 'fixed')
        ->set('value', 10)
        ->call('save')
        ->assertHasErrors(['code' => 'unique']);
});

test('coupon update prevents percent value above 100', function () {
    $admin = User::factory()->admin()->create();
    $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 20]);

    Livewire::actingAs($admin)
        ->test('admin::coupons.edit', ['coupon' => $coupon])
        ->set('code', 'PERCENT120')
        ->set('type', 'percent')
        ->set('value', 120)
        ->call('save')
        ->assertHasErrors(['value' => 'max']);
});

test('admin can delete a coupon from edit page', function () {
    $admin = User::factory()->admin()->create();
    $coupon = Coupon::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin::coupons.edit', ['coupon' => $coupon])
        ->call('delete')
        ->assertRedirect(route('admin.coupons.index'));

    expect(Coupon::find($coupon->id))->toBeNull();
});

test('admin can delete a coupon from index page', function () {
    $admin = User::factory()->admin()->create();
    $coupon = Coupon::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin::coupons.index')
        ->call('delete', $coupon->id);

    expect(Coupon::find($coupon->id))->toBeNull();
});

test('coupons index can search by code', function () {
    $admin = User::factory()->admin()->create();
    Coupon::factory()->create(['code' => 'SAVE20']);
    Coupon::factory()->create(['code' => 'DISCOUNT50']);

    $component = Livewire::actingAs($admin)
        ->test('admin::coupons.index')
        ->set('search', 'SAVE20');

    $component->assertSee('SAVE20');
    $component->assertDontSee('DISCOUNT50');
});

test('coupons index can filter by type', function () {
    $admin = User::factory()->admin()->create();
    Coupon::factory()->create(['type' => 'fixed']);
    Coupon::factory()->percent()->create();

    $component = Livewire::actingAs($admin)
        ->test('admin::coupons.index')
        ->set('filter_type', 'percent');

    $component->assertSee('Percent');
});

test('coupons index shows infinity for unlimited usage', function () {
    $admin = User::factory()->admin()->create();
    Coupon::factory()->create([
        'code' => 'UNLIMITED',
        'used_count' => 5,
        'usage_limit' => null,
    ]);

    Livewire::actingAs($admin)
        ->test('admin::coupons.index')
        ->assertSee('5 / ∞');
});
