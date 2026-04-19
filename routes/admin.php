<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/', 'admin::dashboard')->name('dashboard');

    Route::livewire('categories', 'admin::categories.index')->name('categories.index');
    Route::livewire('categories/create', 'admin::categories.create')->name('categories.create');
    Route::livewire('categories/{category}/edit', 'admin::categories.edit')->name('categories.edit');

    Route::livewire('brands', 'admin::brands.index')->name('brands.index');
    Route::livewire('brands/create', 'admin::brands.create')->name('brands.create');
    Route::livewire('brands/{brand}/edit', 'admin::brands.edit')->name('brands.edit');

    Route::livewire('products', 'admin::products.index')->name('products.index');
    Route::livewire('products/create', 'admin::products.create')->name('products.create');
    Route::livewire('products/{product}/edit', 'admin::products.edit')->name('products.edit');

    Route::livewire('orders', 'admin::orders.index')->name('orders.index');
    Route::livewire('orders/{order}', 'admin::orders.show')->name('orders.show');

    Route::livewire('coupons', 'admin::coupons.index')->name('coupons.index');
    Route::livewire('coupons/create', 'admin::coupons.create')->name('coupons.create');
    Route::livewire('coupons/{coupon}/edit', 'admin::coupons.edit')->name('coupons.edit');

    Route::livewire('banners', 'admin::banners.index')->name('banners.index');
    Route::livewire('banners/create', 'admin::banners.create')->name('banners.create');
    Route::livewire('banners/{banner}/edit', 'admin::banners.edit')->name('banners.edit');

    Route::livewire('users', 'admin::users.index')->name('users.index');
    Route::livewire('users/{user}', 'admin::users.show')->name('users.show');
});
