<?php

use App\Models\Brand;
use App\Models\User;
use Livewire\Livewire;

test('admin can view brands index', function () {
    $admin = User::factory()->admin()->create();
    Brand::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.brands.index'))
        ->assertSuccessful()
        ->assertSee('Brands');
});

test('admin can view create brand page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.brands.create'))
        ->assertSuccessful()
        ->assertSee('Create Brand');
});

test('admin can create a brand', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin.brands.create')
        ->set('name', 'New Brand')
        ->call('save')
        ->assertRedirect(route('admin.brands.edit', Brand::first()));

    expect(Brand::first())->name->toBe('New Brand');
});

test('admin can create a featured brand', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin.brands.create')
        ->set('name', 'Featured Brand')
        ->set('is_featured', true)
        ->call('save');

    $brand = Brand::first();
    expect($brand)->name->toBe('Featured Brand');
    expect($brand->is_featured)->toBeTrue();
});

test('brand creation requires a name', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin.brands.create')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('admin can view edit brand page', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.brands.edit', $brand))
        ->assertSuccessful()
        ->assertSee('Edit Brand');
});

test('admin can update a brand', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create(['name' => 'Old Brand']);

    Livewire::actingAs($admin)
        ->test('admin.brands.edit', ['brand' => $brand])
        ->set('name', 'Updated Brand')
        ->call('save');

    expect($brand->fresh())->name->toBe('Updated Brand');
});

test('admin can delete a brand from edit page', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin.brands.edit', ['brand' => $brand])
        ->call('delete')
        ->assertRedirect(route('admin.brands.index'));

    expect(Brand::find($brand->id))->toBeNull();
});

test('admin can delete a brand from index page', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin.brands.index')
        ->call('delete', $brand->id);

    expect(Brand::find($brand->id))->toBeNull();
});

test('brands index can search by name', function () {
    $admin = User::factory()->admin()->create();
    Brand::factory()->create(['name' => 'Nike']);
    Brand::factory()->create(['name' => 'Adidas']);

    $component = Livewire::actingAs($admin)
        ->test('admin.brands.index')
        ->set('search', 'Nike');

    $component->assertSee('Nike');
    $component->assertDontSee('Adidas');
});

test('brands index can filter by featured', function () {
    $admin = User::factory()->admin()->create();
    Brand::factory()->featured()->create(['name' => 'Featured Brand']);
    Brand::factory()->create(['name' => 'Regular Brand']);

    $component = Livewire::actingAs($admin)
        ->test('admin.brands.index')
        ->set('filter_featured', 'yes');

    $component->assertSee('Featured Brand');
    $component->assertDontSee('Regular Brand');
});

test('updating brand requires a name', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin.brands.edit', ['brand' => $brand])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});
