<?php

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Route;

test('admin routes include auth, verified, and admin middleware', function (string $routeName) {
    $route = Route::getRoutes()->getByName($routeName);

    expect($route)->not->toBeNull();

    $middleware = $route->middleware();

    expect($middleware)->toContain('auth');
    expect($middleware)->toContain('verified');
    expect($middleware)->toContain('admin');
})->with('admin-routes');

test('guests are redirected from all admin routes', function (string $routeName) {
    $this->get(route($routeName, adminRouteParameters($routeName)))
        ->assertRedirect(route('login'));
})->with('admin-routes');

test('non-admin users get 403 on all admin routes', function (string $routeName) {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->get(route($routeName, adminRouteParameters($routeName)))
        ->assertForbidden();
})->with('admin-routes');

test('admin users can access all admin routes', function (string $routeName) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route($routeName, adminRouteParameters($routeName)))
        ->assertSuccessful();
})->with('admin-routes');

test('admin gets 404 for missing bound resources on admin edit/show routes', function (string $routeName, string $parameter) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route($routeName, [$parameter => 999999]))
        ->assertNotFound();
})->with([
    ['admin.categories.edit', 'category'],
    ['admin.brands.edit', 'brand'],
    ['admin.products.edit', 'product'],
    ['admin.orders.show', 'order'],
    ['admin.coupons.edit', 'coupon'],
    ['admin.banners.edit', 'banner'],
    ['admin.users.show', 'user'],
]);

function adminRouteParameters(string $routeName): array
{
    return match ($routeName) {
        'admin.categories.edit' => ['category' => Category::factory()->create()],
        'admin.brands.edit' => ['brand' => Brand::factory()->create()],
        'admin.products.edit' => ['product' => Product::factory()->create()],
        'admin.orders.show' => ['order' => Order::factory()->create()],
        'admin.coupons.edit' => ['coupon' => Coupon::factory()->create()],
        'admin.banners.edit' => ['banner' => Banner::factory()->create()],
        'admin.users.show' => ['user' => User::factory()->create()],
        default => [],
    };
}

dataset('admin-routes', [
    'dashboard' => 'admin.dashboard',
    'categories.index' => 'admin.categories.index',
    'categories.create' => 'admin.categories.create',
    'categories.edit' => 'admin.categories.edit',
    'brands.index' => 'admin.brands.index',
    'brands.create' => 'admin.brands.create',
    'brands.edit' => 'admin.brands.edit',
    'products.index' => 'admin.products.index',
    'products.create' => 'admin.products.create',
    'products.edit' => 'admin.products.edit',
    'orders.index' => 'admin.orders.index',
    'orders.show' => 'admin.orders.show',
    'coupons.index' => 'admin.coupons.index',
    'coupons.create' => 'admin.coupons.create',
    'coupons.edit' => 'admin.coupons.edit',
    'banners.index' => 'admin.banners.index',
    'banners.create' => 'admin.banners.create',
    'banners.edit' => 'admin.banners.edit',
    'users.index' => 'admin.users.index',
    'users.show' => 'admin.users.show',
]);
