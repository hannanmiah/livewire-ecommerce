<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages.home')->name('home');

// Product Listing
Route::livewire('/products', 'pages.products.index')->name('products.index');
Route::livewire('/category/{category:slug}', 'pages.products.index')->name('products.by-category');
Route::livewire('/brand/{brand:slug}', 'pages.products.index')->name('products.by-brand');

// Product Detail
Route::livewire('/product/{product:slug}', 'pages.products.show')->name('products.show');

// Cart (public - session based)
Route::livewire('/cart', 'pages.cart')->name('cart');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Checkout
    Route::livewire('/checkout', 'pages.checkout')->name('checkout');
    Route::livewire('/checkout/{order}', 'pages.checkout.payment')->name('checkout.payment');

    // Customer Dashboard
    Route::redirect('account', 'account/orders');
    Route::livewire('account/orders', 'pages.account.orders')->name('account.orders');
    Route::livewire('account/orders/{order}', 'pages.account.order-detail')->name('account.orders.show');
    Route::livewire('account/addresses', 'pages.account.addresses')->name('account.addresses');
    Route::livewire('account/reviews', 'pages.account.reviews')->name('account.reviews');

    // Dashboard (keep existing)
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
