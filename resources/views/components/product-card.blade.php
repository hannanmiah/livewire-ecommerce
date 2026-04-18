@props(['product'])

@php
    $minPrice = $product->variants->min('price');
    $maxPrice = $product->variants->max('price');
    $minSalePrice = $product->variants->whereNotNull('sale_price')->min('sale_price');
    $hasSale = $product->variants->contains(fn ($v) => $v->is_on_sale);
    $thumbnailUrl = $product->getFirstMediaUrl('thumbnail');
@endphp

<a href="{{ route('products.show', $product->slug) }}" wire:navigate
   class="group block rounded-xl border border-zinc-200 bg-white overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1 dark:border-zinc-700 dark:bg-zinc-800">
    <div class="aspect-square overflow-hidden bg-zinc-100 dark:bg-zinc-700">
        @if($thumbnailUrl)
            <img src="{{ $thumbnailUrl }}" alt="{{ $product->name }}"
                 class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy" />
        @else
            <div class="flex h-full w-full items-center justify-center text-zinc-400 dark:text-zinc-500">
                <svg class="size-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                </svg>
            </div>
        @endif
    </div>

    <div class="p-4">
        @if($product->brand)
            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                {{ $product->brand->name }}
            </p>
        @endif

        <h3 class="mt-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100 line-clamp-2">
            {{ $product->name }}
        </h3>

        <div class="mt-2 flex items-center gap-2">
            @if($hasSale && $minSalePrice)
                <span class="text-sm font-bold text-red-600 dark:text-red-400">
                    {{ config('app.currency_symbol', '$') }}{{ number_format($minSalePrice, 2) }}
                </span>
                <span class="text-xs text-zinc-400 line-through">
                    {{ config('app.currency_symbol', '$') }}{{ number_format($minPrice, 2) }}
                </span>
                <span class="rounded bg-red-100 px-1.5 py-0.5 text-xs font-semibold text-red-600 dark:bg-red-900/30 dark:text-red-400">
                    Sale
                </span>
            @elseif($minPrice === $maxPrice)
                <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                    {{ config('app.currency_symbol', '$') }}{{ number_format($minPrice, 2) }}
                </span>
            @else
                <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                    {{ config('app.currency_symbol', '$') }}{{ number_format($minPrice, 2) }} - {{ config('app.currency_symbol', '$') }}{{ number_format($maxPrice, 2) }}
                </span>
            @endif
        </div>
    </div>
</a>
