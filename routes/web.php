<?php

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $heroBanners = Banner::where('category', 'home')
        ->where('position', 'hero')
        ->whereNotNull('featured_at')
        ->where('featured_at', '<=', now())
        ->with('media')
        ->orderBy('featured_at', 'desc')
        ->get();

    $featuredCategories = Category::whereNotNull('featured_at')
        ->where('featured_at', '<=', now())
        ->withCount('products')
        ->orderBy('featured_at', 'desc')
        ->get();

    $featuredProducts = Product::with(['brand', 'media', 'variants' => fn ($q) => $q->orderBy('price')])
        ->available()
        ->featured()
        ->latest()
        ->take(8)
        ->get();

    $newArrivals = Product::with(['brand', 'media', 'variants' => fn ($q) => $q->orderBy('price')])
        ->available()
        ->latest()
        ->take(8)
        ->get();

    $featuredBrands = Brand::whereNotNull('featured_at')
        ->where('featured_at', '<=', now())
        ->withCount('products')
        ->orderBy('featured_at', 'desc')
        ->get();

    $sectionalBanners = Banner::where('category', 'home')
        ->whereIn('position', ['home_top', 'home_middle', 'home_bottom'])
        ->whereNotNull('featured_at')
        ->where('featured_at', '<=', now())
        ->with('media')
        ->get()
        ->keyBy('position');

    return view('welcome', compact(
        'heroBanners', 'featuredCategories', 'featuredProducts',
        'newArrivals', 'featuredBrands', 'sectionalBanners'
    ));
})->name('home');

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
