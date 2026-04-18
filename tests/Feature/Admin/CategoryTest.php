<?php

use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;

test('admin can view categories index', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertSuccessful()
        ->assertSee('Categories');
});

test('admin can view create category page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.categories.create'))
        ->assertSuccessful()
        ->assertSee('Create Category');
});

test('admin can create a category', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin.categories.create')
        ->set('name', 'New Category')
        ->call('save')
        ->assertRedirect(route('admin.categories.edit', Category::first()));

    expect(Category::first())->name->toBe('New Category');
});

test('admin can create a featured category', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin.categories.create')
        ->set('name', 'Featured Category')
        ->set('is_featured', true)
        ->call('save')
        ->assertRedirect(route('admin.categories.edit', Category::first()));

    $category = Category::first();
    expect($category)->name->toBe('Featured Category');
    expect($category->is_featured)->toBeTrue();
});

test('admin can create a child category', function () {
    $admin = User::factory()->admin()->create();
    $parent = Category::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin.categories.create')
        ->set('name', 'Child Category')
        ->set('parent_id', $parent->id)
        ->call('save')
        ->assertRedirect(route('admin.categories.edit', Category::where('name', 'Child Category')->first()));

    $category = Category::where('name', 'Child Category')->first();
    expect($category)->parent_id->toBe($parent->id);
});

test('category creation requires a name', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin.categories.create')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('admin can view edit category page', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.categories.edit', $category))
        ->assertSuccessful()
        ->assertSee('Edit Category');
});

test('admin can update a category', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($admin)
        ->test('admin.categories.edit', ['category' => $category])
        ->set('name', 'Updated Name')
        ->call('save');

    expect($category->fresh())->name->toBe('Updated Name');
});

test('admin can delete a category from edit page', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin.categories.edit', ['category' => $category])
        ->call('delete')
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::find($category->id))->toBeNull();
});

test('admin can delete a category from index page', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin.categories.index')
        ->call('delete', $category->id);

    expect(Category::find($category->id))->toBeNull();
});

test('categories index can search by name', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->create(['name' => 'Electronics']);
    Category::factory()->create(['name' => 'Clothing']);

    $component = Livewire::actingAs($admin)
        ->test('admin.categories.index')
        ->set('search', 'Electro');

    $component->assertSee('Electronics');
    $component->assertDontSee('Clothing');
});

test('categories index can filter by featured', function () {
    $admin = User::factory()->admin()->create();
    Category::factory()->featured()->create(['name' => 'Featured Cat']);
    Category::factory()->create(['name' => 'Regular Cat']);

    $component = Livewire::actingAs($admin)
        ->test('admin.categories.index')
        ->set('filter_featured', 'yes');

    $component->assertSee('Featured Cat');
    $component->assertDontSee('Regular Cat');
});

test('updating category requires a name', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin.categories.edit', ['category' => $category])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});
