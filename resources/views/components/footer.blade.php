@props([])
@php
    $footerCategories = \App\Models\Category::whereNotNull('featured_at')
        ->where('featured_at', '<=', now())
        ->whereNull('parent_id')
        ->orderBy('featured_at', 'desc')
        ->limit(6)
        ->get();
@endphp

<footer class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 mt-auto">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            {{-- About Us --}}
            <div>
                <flux:heading size="sm" class="mb-4">{{ __('About Us') }}</flux:heading>
                <flux:text class="text-sm leading-relaxed">
                    {{ config('app.name') }} is your trusted online store for quality products at great prices. We are committed to providing the best shopping experience.
                </flux:text>
                {{-- Social Links Placeholder --}}
                <div class="mt-4 flex gap-3">
                    <a href="#" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 4.557a9.83 9.83 0 01-2.828.775 4.932 4.932 0 002.165-2.724 9.864 9.864 0 01-3.127 1.195 4.916 4.916 0 00-8.384 4.482A13.944 13.944 0 011.671 3.149a4.916 4.916 0 001.523 6.574 4.897 4.897 0 01-2.229-.616v.062a4.917 4.917 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.917 4.917 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.945 13.945 0 007.548 2.212c9.057 0 14.01-7.503 14.01-14.01 0-.213-.005-.425-.014-.636A10.012 10.012 0 0024 4.557z" /></svg>
                    </a>
                    <a href="#" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" /></svg>
                    </a>
                    <a href="#" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.6.11.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z" /></svg>
                    </a>
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <flux:heading size="sm" class="mb-4">{{ __('Quick Links') }}</flux:heading>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('products.index') }}" wire:navigate class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                            {{ __('All Products') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('cart') }}" wire:navigate class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                            {{ __('Shopping Cart') }}
                        </a>
                    </li>
                    @auth
                        <li>
                            <a href="{{ route('account.orders') }}" wire:navigate class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                                {{ __('My Orders') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.addresses') }}" wire:navigate class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                                {{ __('My Addresses') }}
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                                {{ __('Log in') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('register') }}" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                                {{ __('Create Account') }}
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>

            {{-- Featured Categories --}}
            <div>
                <flux:heading size="sm" class="mb-4">{{ __('Categories') }}</flux:heading>
                <ul class="space-y-2">
                    @foreach($footerCategories as $category)
                        <li>
                            <a href="{{ route('products.by-category', $category->slug) }}" wire:navigate class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Customer Service --}}
            <div>
                <flux:heading size="sm" class="mb-4">{{ __('Customer Service') }}</flux:heading>
                <ul class="space-y-2">
                    <li>
                        <a href="#" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                            {{ __('Contact Us') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                            {{ __('Shipping Policy') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                            {{ __('Return Policy') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                            {{ __('Privacy Policy') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                            {{ __('Terms of Service') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <flux:separator class="my-8" />

        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <flux:text class="text-xs">
                &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
            </flux:text>
        </div>
    </div>
</footer>
