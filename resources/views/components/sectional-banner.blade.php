@props(['banner'])

@php
    $imageUrl = $banner->getFirstMediaUrl('image');
@endphp

@if($banner)
    <section class="relative overflow-hidden rounded-xl">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $banner->title }}" class="h-64 w-full object-cover sm:h-80 lg:h-96" loading="lazy" />
        @endif

        <div class="{{ $imageUrl ? 'absolute inset-0 bg-black/40' : 'bg-gradient-to-r from-zinc-800 to-zinc-600' }} flex items-center">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-lg">
                    <h2 class="text-2xl font-bold text-white sm:text-3xl lg:text-4xl">
                        {{ $banner->title }}
                    </h2>

                    @if($banner->description)
                        <p class="mt-3 text-base text-white/80 sm:text-lg">
                            {{ $banner->description }}
                        </p>
                    @endif

                    @if($banner->link)
                        <flux:button
                            variant="primary"
                            :href="$banner->link"
                            class="mt-5"
                        >
                            {{ __('Shop Now') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
