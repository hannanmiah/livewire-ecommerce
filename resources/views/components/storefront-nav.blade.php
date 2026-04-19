@props([])
@php
    $featuredCategories = \App\Models\Category::whereNotNull('featured_at')
        ->where('featured_at', '<=', now())
        ->whereNull('parent_id')
        ->orderBy('featured_at', 'desc')
        ->limit(5)
        ->get();

    $cartCount = 0;
    if (auth()->check()) {
        $navCart = \App\Models\Cart::where('user_id', auth()->id())->first();
    } else {
        $navCart = \App\Models\Cart::where('session_id', session()->getId())->first();
    }
    if ($navCart) {
        $cartCount = $navCart->items()->count();
    }
@endphp

<flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

    <x-app-logo href="{{ route('home') }}" wire:navigate />

    {{-- Desktop Navigation --}}
    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item icon="shopping-bag" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
            {{ __('Products') }}
        </flux:navbar.item>

        <flux:separator vertical variant="subtle" class="my-2" />

        <flux:dropdown class="max-lg:hidden">
            <flux:navbar.item icon:trailing="chevron-down">{{ __('Categories') }}</flux:navbar.item>
            <flux:navmenu>
                @foreach($featuredCategories as $category)
                    <flux:navmenu.item :href="route('products.by-category', $category->slug)" wire:navigate>
                        {{ $category->name }}
                    </flux:navmenu.item>
                @endforeach
                @if($featuredCategories->isNotEmpty())
                    <flux:menu.separator />
                @endif
                <flux:navmenu.item :href="route('products.index')" wire:navigate>
                    {{ __('All Products') }}
                </flux:navmenu.item>
            </flux:navmenu>
        </flux:dropdown>
    </flux:navbar>

    <flux:spacer />

    {{-- Search Bar (Desktop) --}}
    <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
        <form action="{{ route('products.index') }}" method="GET" wire:navigate class="flex items-center" x-data="{ search: new URLSearchParams(window.location.search).get('search') || '' }">
            <flux:input
                x-model="search"
                name="search"
                placeholder="{{ __('Search products...') }}"
                icon="magnifying-glass"
                size="sm"
                class="w-48 xl:w-64"
            />
        </form>

        {{-- Cart Dropdown --}}
        <livewire:cart-icon />

        @auth
            {{-- User Menu --}}
            <flux:dropdown position="bottom" align="end">
                <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="user" :label="auth()->user()->name" />
                <flux:menu>
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar
                            :name="auth()->user()->name"
                            :initials="auth()->user()->initials()"
                        />
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="truncate text-xs">{{ auth()->user()->email }}</flux:text>
                        </div>
                    </div>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item icon="shopping-bag" :href="route('account.orders')" wire:navigate>
                            {{ __('My Orders') }}
                        </flux:menu.item>
                        <flux:menu.item icon="map-pin" :href="route('account.addresses')" wire:navigate>
                            {{ __('Addresses') }}
                        </flux:menu.item>
                        <flux:menu.item icon="star" :href="route('account.reviews')" wire:navigate>
                            {{ __('Reviews') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item icon="cog-6-tooth" :href="route('profile.edit')" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                        @if(auth()->user()->role === 'admin')
                            <flux:menu.item icon="shield-check" :href="route('admin.dashboard')" wire:navigate>
                                {{ __('Admin Panel') }}
                            </flux:menu.item>
                        @endif
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        @else
            <flux:tooltip :content="__('Log in')" position="bottom">
                <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="arrow-right-start-on-rectangle" :href="route('login')" :label="__('Log in')" />
            </flux:tooltip>
            <flux:button
                variant="primary"
                size="sm"
                :href="route('register')"
                wire:navigate
                class="max-lg:hidden"
            >
                {{ __('Register') }}
            </flux:button>
        @endauth
    </flux:navbar>
</flux:header>

{{-- Mobile Menu Sidebar --}}
<flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.header>
        <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    {{-- Mobile Search --}}
    <form action="{{ route('products.index') }}" method="GET" wire:navigate class="px-3 py-2">
        <flux:input
            name="search"
            placeholder="{{ __('Search products...') }}"
            icon="magnifying-glass"
            size="sm"
        />
    </form>

    <flux:sidebar.nav>
        <flux:sidebar.group :heading="__('Shop')" class="grid">
            <flux:sidebar.item icon="shopping-bag" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                {{ __('All Products') }}
            </flux:sidebar.item>
            @foreach($featuredCategories as $category)
                <flux:sidebar.item icon="folder" :href="route('products.by-category', $category->slug)" wire:navigate>
                    {{ $category->name }}
                </flux:sidebar.item>
            @endforeach
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:spacer />

    <flux:sidebar.nav>
        <flux:sidebar.item icon="shopping-cart" :href="route('cart')" wire:navigate>
            {{ __('Cart') }}
            @if($cartCount > 0)
                <flux:badge color="primary" size="sm" class="ml-2">{{ $cartCount }}</flux:badge>
            @endif
        </flux:sidebar.item>

        @auth
            <flux:sidebar.item icon="shopping-bag" :href="route('account.orders')" wire:navigate>
                {{ __('My Orders') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" wire:navigate>
                {{ __('Settings') }}
            </flux:sidebar.item>
            @if(auth()->user()->role === 'admin')
                <flux:sidebar.item icon="shield-check" :href="route('admin.dashboard')" wire:navigate>
                    {{ __('Admin Panel') }}
                </flux:sidebar.item>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:sidebar.item icon="arrow-right-start-on-rectangle" as="button" type="submit">
                    {{ __('Log out') }}
                </flux:sidebar.item>
            </form>
        @else
            <flux:sidebar.item icon="arrow-right-start-on-rectangle" :href="route('login')">
                {{ __('Log in') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="user-plus" :href="route('register')">
                {{ __('Register') }}
            </flux:sidebar.item>
        @endauth
    </flux:sidebar.nav>
</flux:sidebar>
