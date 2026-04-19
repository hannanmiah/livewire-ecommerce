<?php

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function heroBanners()
    {
        return Banner::where('category', 'home')
            ->where('position', 'hero')
            ->whereNotNull('featured_at')
            ->where('featured_at', '<=', now())
            ->with('media')
            ->orderBy('featured_at', 'desc')
            ->get();
    }

    #[Computed]
    public function featuredCategories()
    {
        return Category::whereNotNull('featured_at')
            ->where('featured_at', '<=', now())
            ->whereNull('parent_id')
            ->withCount('products')
            ->orderBy('featured_at', 'desc')
            ->get();
    }

    #[Computed]
    public function featuredProducts()
    {
        return Product::with(['brand', 'media', 'variants' => fn($q) => $q->orderBy('price')])
            ->available()
            ->featured()
            ->latest()
            ->take(8)
            ->get();
    }

    #[Computed]
    public function newArrivals()
    {
        return Product::with(['brand', 'media', 'variants' => fn($q) => $q->orderBy('price')])
            ->available()
            ->latest()
            ->take(8)
            ->get();
    }

    #[Computed]
    public function featuredBrands()
    {
        return Brand::whereNotNull('featured_at')
            ->where('featured_at', '<=', now())
            ->withCount('products')
            ->orderBy('featured_at', 'desc')
            ->get();
    }

    #[Computed]
    public function sectionalBanners()
    {
        return Banner::where('category', 'home')
            ->whereIn('position', ['home_top', 'home_middle', 'home_bottom'])
            ->whereNotNull('featured_at')
            ->where('featured_at', '<=', now())
            ->with('media')
            ->get()
            ->keyBy('position');
    }
}; ?>
<div>
    {{-- Section 1: Hero Banner Slider --}}
    @if($this->heroBanners->isNotEmpty())
        <section class="relative">
            @if($this->heroBanners->count() === 1)
                @php $banner = $this->heroBanners->first(); @endphp
                <div class="relative h-64 overflow-hidden bg-zinc-900 sm:h-80 lg:h-[500px]">
                    @if($bannerImage = $banner->getFirstMediaUrl('image'))
                        <img src="{{ $bannerImage }}" alt="{{ $banner->title }}"
                             class="h-full w-full object-cover"/>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent"></div>
                    <div class="absolute inset-0 flex items-center">
                        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div class="max-w-xl">
                                <h1 class="text-3xl font-bold text-white sm:text-4xl lg:text-5xl">
                                    {{ $banner->title }}
                                </h1>
                                @if($banner->description)
                                    <p class="mt-4 text-base text-white/80 sm:text-lg lg:text-xl">
                                        {{ $banner->description }}
                                    </p>
                                @endif
                                @if($banner->link)
                                    <flux:button variant="primary" size="base" :href="$banner->link" class="mt-6">
                                        {{ __('Shop Now') }}
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div x-data="{
                    current: 0,
                    total: {{ $this->heroBanners->count() }},
                    autoplay: null,
                    startAutoplay() {
                        this.autoplay = setInterval(() => { this.next() }, 5000);
                    },
                    stopAutoplay() {
                        clearInterval(this.autoplay);
                    },
                    next() {
                        this.current = (this.current + 1) % this.total;
                    },
                    prev() {
                        this.current = (this.current - 1 + this.total) % this.total;
                    },
                    goTo(index) {
                        this.current = index;
                    }
                }" x-init="startAutoplay()" @mouseenter="stopAutoplay()" @mouseleave="startAutoplay()"
                     class="relative h-64 overflow-hidden bg-zinc-900 sm:h-80 lg:h-[500px]">

                    @foreach($this->heroBanners as $index => $banner)
                        <div x-show="current === {{ $index }}"
                             x-transition:enter="transition ease-out duration-700"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute inset-0">
                            @if($bannerImage = $banner->getFirstMediaUrl('image'))
                                <img src="{{ $bannerImage }}" alt="{{ $banner->title }}"
                                     class="h-full w-full object-cover"/>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent"></div>
                            <div class="absolute inset-0 flex items-center">
                                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                                    <div class="max-w-xl">
                                        <h1 class="text-3xl font-bold text-white sm:text-4xl lg:text-5xl">
                                            {{ $banner->title }}
                                        </h1>
                                        @if($banner->description)
                                            <p class="mt-4 text-base text-white/80 sm:text-lg lg:text-xl">
                                                {{ $banner->description }}
                                            </p>
                                        @endif
                                        @if($banner->link)
                                            <flux:button variant="primary" size="base" :href="$banner->link"
                                                         class="mt-6">
                                                {{ __('Shop Now') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Navigation Arrows --}}
                    <button @click="prev()"
                            class="absolute left-4 top-1/2 -translate-y-1/2 flex size-10 items-center justify-center rounded-full bg-black/30 text-white backdrop-blur-sm transition-colors hover:bg-black/50 lg:left-8 lg:size-12"
                            aria-label="{{ __('Previous slide') }}">
                        <svg class="size-5 lg:size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                        </svg>
                    </button>
                    <button @click="next()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 flex size-10 items-center justify-center rounded-full bg-black/30 text-white backdrop-blur-sm transition-colors hover:bg-black/50 lg:right-8 lg:size-12"
                            aria-label="{{ __('Next slide') }}">
                        <svg class="size-5 lg:size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>

                    {{-- Navigation Dots --}}
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                        @foreach($this->heroBanners as $index => $banner)
                            <button @click="goTo({{ $index }})"
                                    :class="current === {{ $index }} ? 'bg-white scale-110' : 'bg-white/50 hover:bg-white/75'"
                                    class="size-2.5 rounded-full transition-all duration-300"
                                    aria-label="{{ __('Go to slide') }} {{ $loop->iteration }}">
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif

    {{-- Section 2: Featured Categories --}}
    @if($this->featuredCategories->isNotEmpty())
        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 sm:text-3xl">
                        {{ __('Shop by Category') }}
                    </h2>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Browse our featured categories') }}
                    </p>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                    @foreach($this->featuredCategories as $category)
                        <a href="{{ route('products.by-category', $category->slug) }}" wire:navigate
                           class="group relative flex flex-col items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-gradient-to-br from-zinc-100 to-zinc-50 p-6 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 dark:border-zinc-700 dark:from-zinc-800 dark:to-zinc-750"
                           style="--tw-gradient-to: oklch(0.97 0 0 / 1);">
                            <div class="flex size-14 items-center justify-center rounded-full bg-white shadow-sm dark:bg-zinc-700 transition-transform duration-300 group-hover:scale-110">
                                <svg class="size-7 text-zinc-600 dark:text-zinc-300" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                                </svg>
                            </div>
                            <h3 class="mt-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $category->name }}
                            </h3>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $category->products_count }} {{ Str::plural(__('product'), $category->products_count) }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Section 3: Featured Products --}}
    @if($this->featuredProducts->isNotEmpty())
        <section class="py-12 lg:py-16 bg-zinc-50 dark:bg-zinc-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 sm:text-3xl">
                            {{ __('Featured Products') }}
                        </h2>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Handpicked items just for you') }}
                        </p>
                    </div>
                    <flux:button variant="ghost" :href="route('products.index')" wire:navigate
                                 icon:trailing="arrow-right">
                        {{ __('View All') }}
                    </flux:button>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                    @foreach($this->featuredProducts as $product)
                        <x-product-card :product="$product"/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Section 4: Sectional Banner (home_top) --}}
    @if($this->sectionalBanners->has('home_top'))
        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-sectional-banner :banner="$this->sectionalBanners->get('home_top')"/>
            </div>
        </section>
    @endif

    {{-- Section 5: New Arrivals --}}
    @if($this->newArrivals->isNotEmpty())
        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 sm:text-3xl">
                            {{ __('New Arrivals') }}
                        </h2>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Check out our latest products') }}
                        </p>
                    </div>
                    <flux:button variant="ghost" :href="route('products.index')" wire:navigate
                                 icon:trailing="arrow-right">
                        {{ __('View All') }}
                    </flux:button>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                    @foreach($this->newArrivals as $product)
                        <x-product-card :product="$product"/>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Section 6: Sectional Banner (home_middle) --}}
    @if($this->sectionalBanners->has('home_middle'))
        <section class="py-12 lg:py-16 bg-zinc-50 dark:bg-zinc-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-sectional-banner :banner="$this->sectionalBanners->get('home_middle')"/>
            </div>
        </section>
    @endif

    {{-- Section 7: Featured Brands --}}
    @if($this->featuredBrands->isNotEmpty())
        <section class="py-12 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 sm:text-3xl">
                        {{ __('Featured Brands') }}
                    </h2>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Top brands we trust and recommend') }}
                    </p>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 lg:gap-6">
                    @foreach($this->featuredBrands as $brand)
                        <a href="{{ route('products.by-brand', $brand->slug) }}" wire:navigate
                           class="group flex flex-col items-center justify-center rounded-xl border border-zinc-200 bg-white p-6 transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex size-16 items-center justify-center rounded-full bg-zinc-100 text-lg font-bold text-zinc-600 transition-transform duration-300 group-hover:scale-110 dark:bg-zinc-700 dark:text-zinc-300">
                                {{ Str::upper(Str::substr($brand->name, 0, 2)) }}
                            </div>
                            <h3 class="mt-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100 text-center">
                                {{ $brand->name }}
                            </h3>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $brand->products_count }} {{ Str::plural(__('product'), $brand->products_count) }}
                            </p>
                        </a>
                    @endforeach>
                </div>
            </div>
        </section>
    @endif

    {{-- Section 8: Sectional Banner (home_bottom) --}}
    @if($this->sectionalBanners->has('home_bottom'))
        <section class="py-12 lg:py-16 bg-zinc-50 dark:bg-zinc-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-sectional-banner :banner="$this->sectionalBanners->get('home_bottom')"/>
            </div>
        </section>
    @endif
</div>

