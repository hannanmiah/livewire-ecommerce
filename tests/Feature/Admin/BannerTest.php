<?php

use App\Models\Banner;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('admin can view banners index', function () {
    $admin = User::factory()->admin()->create();
    Banner::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.banners.index'))
        ->assertSuccessful()
        ->assertSee('Banners');
});

test('admin can view create banner page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.banners.create'))
        ->assertSuccessful()
        ->assertSee('Create Banner');
});

test('admin can create a banner with image', function () {
    $admin = User::factory()->admin()->create();

    $image = UploadedFile::fake()->image('banner.jpg');

    Livewire::actingAs($admin)
        ->test('admin.banners.create')
        ->set('title', 'Test Banner')
        ->set('category', 'home')
        ->set('position', 'hero')
        ->set('image', $image)
        ->call('save');

    $banner = Banner::first();
    expect($banner)->not->toBeNull();
    expect($banner)->title->toBe('Test Banner');
    expect($banner)->category->toBe('home');
    expect($banner)->position->toBe('hero');
});

test('admin can create a featured banner with image', function () {
    $admin = User::factory()->admin()->create();

    $image = UploadedFile::fake()->image('banner.jpg');

    Livewire::actingAs($admin)
        ->test('admin.banners.create')
        ->set('title', 'Featured Banner')
        ->set('category', 'home')
        ->set('is_featured', true)
        ->set('image', $image)
        ->call('save');

    expect(Banner::first())->is_featured->toBeTrue();
});

test('banner creation requires a title', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin.banners.create')
        ->set('title', '')
        ->set('category', 'home')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});

test('banner creation requires a valid category', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin.banners.create')
        ->set('title', 'Test Banner')
        ->set('category', 'invalid')
        ->call('save')
        ->assertHasErrors(['category' => 'in']);
});

test('banner creation requires an image', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('admin.banners.create')
        ->set('title', 'Test Banner')
        ->set('category', 'home')
        ->call('save')
        ->assertHasErrors(['image' => 'required']);
});

test('admin can view edit banner page', function () {
    $admin = User::factory()->admin()->create();
    $banner = Banner::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.banners.edit', $banner))
        ->assertSuccessful()
        ->assertSee('Edit Banner');
});

test('admin can update a banner', function () {
    $admin = User::factory()->admin()->create();
    $banner = Banner::factory()->create(['title' => 'Old Title']);

    Livewire::actingAs($admin)
        ->test('admin.banners.edit', ['banner' => $banner])
        ->set('title', 'Updated Title')
        ->call('save');

    expect($banner->fresh())->title->toBe('Updated Title');
});

test('admin can delete a banner from edit page', function () {
    $admin = User::factory()->admin()->create();
    $banner = Banner::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin.banners.edit', ['banner' => $banner])
        ->call('delete')
        ->assertRedirect(route('admin.banners.index'));

    expect(Banner::find($banner->id))->toBeNull();
});

test('admin can delete a banner from index page', function () {
    $admin = User::factory()->admin()->create();
    $banner = Banner::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin.banners.index')
        ->call('delete', $banner->id);

    expect(Banner::find($banner->id))->toBeNull();
});

test('banners index can filter by category', function () {
    $admin = User::factory()->admin()->create();
    Banner::factory()->create(['category' => 'home', 'title' => 'Home Banner']);
    Banner::factory()->create(['category' => 'sidebar', 'title' => 'Sidebar Banner']);

    $component = Livewire::actingAs($admin)
        ->test('admin.banners.index')
        ->set('filter_category', 'home');

    $component->assertSee('Home Banner');
    $component->assertDontSee('Sidebar Banner');
});

test('banners index can filter by position', function () {
    $admin = User::factory()->admin()->create();
    Banner::factory()->create(['position' => 'hero', 'title' => 'Hero Banner']);
    Banner::factory()->create(['position' => 'home_middle', 'title' => 'Middle Banner']);

    $component = Livewire::actingAs($admin)
        ->test('admin.banners.index')
        ->set('filter_position', 'hero');

    $component->assertSee('Hero Banner');
    $component->assertDontSee('Middle Banner');
});
