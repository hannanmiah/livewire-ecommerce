<?php

use App\Models\Product;
use App\Models\ProductVariant;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public Product $product;

    public array $selectedAttributes = [];

    public int $quantity = 1;

    public ?int $reviewRating = null;

    public string $reviewComment = '';

    public function mount(): void
    {
        $this->product->load([
            'brand',
            'category',
            'variants.stock',
            'variants.attributeValues.attribute',
            'reviews.user',
        ]);
    }

    public function getTitleProperty(): string
    {
        return $this->product->name;
    }

    #[Computed]
    public function matchedVariant(): ?ProductVariant
    {
        if (empty($this->selectedAttributes)) {
            return null;
        }

        return $this->product->variants
            ->first(function ($variant) {
                $variantAttrs = $variant->attributeValues->mapWithKeys(fn ($av) => [$av->attribute->name => $av->value]);

                foreach ($this->selectedAttributes as $attrName => $value) {
                    if (($variantAttrs[$attrName] ?? null) !== $value) {
                        return false;
                    }
                }

                return true;
            });
    }

    #[Computed]
    public function variantAttributes()
    {
        $attributes = [];

        foreach ($this->product->variants as $variant) {
            foreach ($variant->attributeValues as $av) {
                $name = $av->attribute->name;

                if (! isset($attributes[$name])) {
                    $attributes[$name] = collect();
                }

                $attributes[$name][$av->id] = $av->value;
            }
        }

        return collect($attributes)->map->unique();
    }

    #[Computed]
    public function images(): \Illuminate\Support\Collection
    {
        $images = collect();

        $thumb = $this->product->getFirstMedia('thumbnail');

        if ($thumb) {
            $images->push($thumb);
        }

        foreach ($this->product->getMedia('gallery') as $media) {
            $images->push($media);
        }

        if ($images->isEmpty()) {
            $images->push(null);
        }

        return $images;
    }

    #[Computed]
    public function averageRating(): float
    {
        return round($this->product->reviews->avg('rating') ?? 0, 1);
    }

    #[Computed]
    public function ratingBreakdown(): array
    {
        $breakdown = [];

        for ($i = 5; $i >= 1; $i--) {
            $breakdown[$i] = $this->product->reviews->where('rating', $i)->count();
        }

        return $breakdown;
    }

    #[Computed]
    public function relatedProducts()
    {
        return Product::with(['brand', 'media', 'variants'])
            ->available()
            ->where('id', '!=', $this->product->id)
            ->where(function ($q) {
                $q->where('category_id', $this->product->category_id)
                    ->orWhere('brand_id', $this->product->brand_id);
            })
            ->inRandomOrder()
            ->take(4)
            ->get();
    }

    #[Computed]
    public function hasReviewed(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return $this->product->reviews()->where('user_id', Auth::id())->exists();
    }

    #[Computed]
    public function priceRange(): array
    {
        $variants = $this->product->variants;

        if ($variants->isEmpty()) {
            return ['min' => null, 'max' => null];
        }

        $prices = $variants->map(fn ($v) => (float) $v->effective_price);

        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
        ];
    }

    public function selectAttribute(string $attributeName, string $value): void
    {
        $this->selectedAttributes[$attributeName] = $value;
        unset($this->matchedVariant);
        $this->quantity = 1;
    }

    public function updatedQuantity(): void
    {
        $variant = $this->matchedVariant;

        if ($variant && $variant->stock && $this->quantity > $variant->stock->available_quantity) {
            $this->quantity = $variant->stock->available_quantity;
        }

        if ($this->quantity < 1) {
            $this->quantity = 1;
        }
    }

    public function addToCart(): void
    {
        $variant = $this->matchedVariant;

        if (! $variant) {
            Flux::toast(variant: 'warning', text: __('Please select all options.'));

            return;
        }

        if (! $variant->stock?->isInStock()) {
            Flux::toast(variant: 'danger', text: __('This variant is out of stock.'));

            return;
        }

        if ($this->quantity > $variant->stock->available_quantity) {
            Flux::toast(variant: 'danger', text: __('Not enough stock available.'));

            return;
        }

        Flux::toast(variant: 'success', text: __('Added to cart!'));
    }

    public function buyNow(): void
    {
        $variant = $this->matchedVariant;

        if (! $variant) {
            Flux::toast(variant: 'warning', text: __('Please select all options.'));

            return;
        }

        $this->redirect(route('checkout'), navigate: true);
    }

    public function submitReview(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));

            return;
        }

        $this->validate([
            'reviewRating' => 'required|integer|min:1|max:5',
            'reviewComment' => 'nullable|string|max:1000',
        ]);

        if ($this->hasReviewed) {
            Flux::toast(variant: 'danger', text: __('You have already reviewed this product.'));

            return;
        }

        $this->product->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $this->reviewRating,
            'comment' => $this->reviewComment,
        ]);

        Flux::toast(variant: 'success', text: __('Review submitted!'));
        $this->reset('reviewRating', 'reviewComment');
        unset($this->product);
        $this->product->load('reviews.user');
    }

    public function isCombinationAvailable(string $attributeName, string $value): bool
    {
        $testAttributes = array_merge($this->selectedAttributes, [$attributeName => $value]);

        return $this->product->variants
            ->contains(function ($variant) use ($testAttributes) {
                $variantAttrs = $variant->attributeValues->mapWithKeys(fn ($av) => [$av->attribute->name => $av->value]);

                foreach ($testAttributes as $attrName => $val) {
                    if (($variantAttrs[$attrName] ?? null) !== $val) {
                        return false;
                    }
                }

                return true;
            });
    }
}; ?>
<div>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('Home') }}</a>
            <flux:icon icon="chevron-right" class="size-3.5" />
            <a href="{{ route('products.index') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('Products') }}</a>
            @if($product->category)
                <flux:icon icon="chevron-right" class="size-3.5" />
                <a href="{{ route('products.by-category', $product->category->slug) }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ $product->category->name }}</a>
            @endif
            <flux:icon icon="chevron-right" class="size-3.5" />
            <span class="text-zinc-700 dark:text-zinc-300">{{ $product->name }}</span>
        </nav>

        {{-- Main Product Section --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            {{-- Left: Image Gallery --}}
            <div x-data="{ activeIndex: 0 }" class="space-y-4">
                {{-- Main Image --}}
                <div class="relative aspect-square overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                    @foreach($this->images as $index => $image)
                        <img
                            x-show="activeIndex === {{ $index }}"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            src="{{ $image?->getUrl() ?? asset('images/placeholder.png') }}"
                            alt="{{ $product->name }}"
                            class="size-full object-cover"
                        />
                    @endforeach
                    @if($this->images->first() === null)
                        <div class="flex size-full items-center justify-center">
                            <flux:icon icon="photo" class="size-16 text-zinc-300 dark:text-zinc-600" />
                        </div>
                    @endif
                </div>

                {{-- Thumbnail Strip --}}
                @if($this->images->count() > 1)
                    <div class="flex gap-2 overflow-x-auto pb-1">
                        @foreach($this->images as $index => $image)
                            <button
                                type="button"
                                @click="activeIndex = {{ $index }}"
                                :class="activeIndex === {{ $index }} ? 'ring-2 ring-zinc-900 dark:ring-zinc-100' : 'ring-1 ring-zinc-200 dark:ring-zinc-700'"
                                class="size-16 flex-shrink-0 overflow-hidden rounded-md"
                            >
                                <img
                                    src="{{ $image?->getUrl('thumb') ?? $image?->getUrl() ?? asset('images/placeholder.png') }}"
                                    alt="{{ $product->name }}"
                                    class="size-full object-cover"
                                />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right: Product Info --}}
            <div class="space-y-6">
                {{-- Brand --}}
                @if($product->brand)
                    <a href="{{ route('products.by-brand', $product->brand->slug) }}" wire:navigate class="inline-block text-sm font-medium text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300">
                        {{ $product->brand->name }}
                    </a>
                @endif

                {{-- Product Name --}}
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 sm:text-3xl">
                    {{ $product->name }}
                </h1>

                {{-- Rating Summary --}}
                @if($product->reviews->isNotEmpty())
                    <div class="flex items-center gap-2">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($this->averageRating))
                                    <svg class="size-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                @elseif($i - 0.5 <= $this->averageRating)
                                    <svg class="size-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" clip-rule="evenodd" fill-rule="evenodd" /></svg>
                                @else
                                    <svg class="size-5 text-zinc-300 dark:text-zinc-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                @endif
                            @endfor
                        </div>
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $this->averageRating }} ({{ $product->reviews->count() }} {{ __('reviews') }})
                        </span>
                    </div>
                @endif

                {{-- Price --}}
                <div class="space-y-1">
                    @php
                        $variant = $this->matchedVariant;
                        $range = $this->priceRange;
                    @endphp

                    @if($variant)
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                                ${{ number_format((float) $variant->effective_price, 2) }}
                            </span>
                            @if($variant->is_on_sale)
                                <span class="text-lg text-zinc-400 line-through">
                                    ${{ number_format((float) $variant->price, 2) }}
                                </span>
                                <flux:badge color="red" size="sm">{{ __('Sale') }}</flux:badge>
                            @endif
                        </div>
                    @elseif($range['min'] !== null)
                        @if($range['min'] === $range['max'])
                            <span class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                                ${{ number_format($range['min'], 2) }}
                            </span>
                        @else
                            <span class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                                {{ __('From :price', ['price' => '$' . number_format($range['min'], 2)]) }}
                            </span>
                        @endif
                    @endif
                </div>

                {{-- Description --}}
                @if($product->description)
                    <div class="prose prose-sm max-w-none text-zinc-600 dark:text-zinc-400">
                        {{ $product->description }}
                    </div>
                @endif

                <flux:separator />

                {{-- Variant Selector --}}
                @if($this->variantAttributes->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($this->variantAttributes as $attributeName => $values)
                            <div>
                                <flux:text class="mb-2 text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                    {{ $attributeName }}:
                                    @isset($selectedAttributes[$attributeName])
                                        <span class="font-normal text-zinc-500 dark:text-zinc-400">{{ $selectedAttributes[$attributeName] }}</span>
                                    @endisset
                                </flux:text>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($values as $value)
                                        @php
                                            $isSelected = ($selectedAttributes[$attributeName] ?? null) === $value;
                                            $isAvailable = $this->isCombinationAvailable($attributeName, $value);
                                        @endphp
                                        <button
                                            type="button"
                                            wire:click="selectAttribute('{{ $attributeName }}', '{{ $value }}')"
                                            @if(! $isAvailable) disabled @endif
                                            @class([
                                                'rounded-md border px-4 py-2 text-sm font-medium transition-colors',
                                                'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' => $isSelected,
                                                'border-zinc-200 bg-white text-zinc-700 hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-zinc-600' => ! $isSelected && $isAvailable,
                                                'cursor-not-allowed border-zinc-200 bg-zinc-100 text-zinc-400 opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-600' => ! $isAvailable,
                                            ])
                                        >
                                            {{ $value }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Stock Status --}}
                @if($variant)
                    @if($variant->stock?->isInStock())
                        <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                            <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                            {{ __('In Stock (:count available)', ['count' => $variant->stock->available_quantity]) }}
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                            <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                            {{ __('Out of Stock') }}
                        </div>
                    @endif
                @endif

                {{-- Quantity + Actions --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <flux:field>
                            <flux:label>{{ __('Quantity') }}</flux:label>
                            <flux:input
                                type="number"
                                wire:model.live="quantity"
                                min="1"
                                max="{{ $variant?->stock?->available_quantity ?? 99 }}"
                                class="w-24"
                            />
                        </flux:field>
                    </div>

                    <div class="flex items-center gap-3">
                        <flux:button
                            variant="primary"
                            wire:click="addToCart"
                            class="flex-1"
                            icon="shopping-cart"
                        >
                            {{ __('Add to Cart') }}
                        </flux:button>

                        <flux:button
                            variant="outline"
                            wire:click="buyNow"
                            class="flex-1"
                        >
                            {{ __('Buy Now') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>

        <flux:separator class="my-10" />

        {{-- Reviews Section --}}
        <section id="reviews" class="space-y-8">
            <flux:heading level="lg">{{ __('Customer Reviews') }}</flux:heading>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                {{-- Rating Summary --}}
                <div class="space-y-4 rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->averageRating }}</div>
                        <div class="mt-1 flex items-center justify-center">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($this->averageRating))
                                    <svg class="size-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                @else
                                    <svg class="size-5 text-zinc-300 dark:text-zinc-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                @endif
                            @endfor
                        </div>
                        <flux:text class="mt-1">{{ $product->reviews->count() }} {{ __('reviews') }}</flux:text>
                    </div>

                    {{-- Rating Breakdown --}}
                    <div class="space-y-2">
                        @foreach($this->ratingBreakdown as $stars => $count)
                            @php
                                $total = $product->reviews->count();
                                $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-2 text-sm">
                                <span class="w-8 text-zinc-500 dark:text-zinc-400">{{ $stars }}{{ __('star') }}</span>
                                <div class="flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                    <div class="h-2 rounded-full bg-amber-400" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="w-6 text-right text-zinc-500 dark:text-zinc-400">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Review List --}}
                <div class="space-y-4 lg:col-span-2">
                    @if($product->reviews->isEmpty())
                        <div class="rounded-lg border border-zinc-200 p-8 text-center dark:border-zinc-700">
                            <flux:icon icon="chat-bubble-left-right" class="mx-auto mb-3 size-10 text-zinc-300 dark:text-zinc-600" />
                            <flux:text>{{ __('No reviews yet. Be the first to review this product!') }}</flux:text>
                        </div>
                    @else
                        @foreach($product->reviews as $review)
                            <div wire:key="review-{{ $review->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <flux:avatar :name="$review->user->name" size="xs" />
                                        <div>
                                            <flux:text variant="strong">{{ $review->user->name }}</flux:text>
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->rating)
                                                        <svg class="size-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                                    @else
                                                        <svg class="size-4 text-zinc-300 dark:text-zinc-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <flux:text class="text-xs">{{ $review->created_at->diffForHumans() }}</flux:text>
                                </div>
                                @if($review->comment)
                                    <flux:text class="mt-2 block">{{ $review->comment }}</flux:text>
                                @endif
                            </div>
                        @endforeach
                    @endif

                    {{-- Review Form --}}
                    @auth
                        @if($this->hasReviewed)
                            <div class="rounded-lg border border-zinc-200 p-4 text-center dark:border-zinc-700">
                                <flux:text class="text-sm text-zinc-500">{{ __('You have already reviewed this product.') }}</flux:text>
                            </div>
                        @else
                            <form wire:submit="submitReview" class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                <flux:heading size="md">{{ __('Write a Review') }}</flux:heading>

                                <flux:field>
                                    <flux:label>{{ __('Rating') }}</flux:label>
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button
                                                type="button"
                                                wire:click="$set('reviewRating', {{ $i }})"
                                                class="cursor-pointer p-0.5"
                                            >
                                                @if($reviewRating && $i <= $reviewRating)
                                                    <svg class="size-7 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                                @else
                                                    <svg class="size-7 text-zinc-300 dark:text-zinc-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                                @endif
                                            </button>
                                        @endfor
                                    </div>
                                    <flux:error name="reviewRating" />
                                </flux:field>

                                <flux:field>
                                    <flux:textarea
                                        wire:model="reviewComment"
                                        :placeholder="__('Share your experience with this product...')"
                                        rows="4"
                                    />
                                    <flux:error name="reviewComment" />
                                </flux:field>

                                <flux:button type="submit" variant="primary">{{ __('Submit Review') }}</flux:button>
                            </form>
                        @endif
                    @else
                        <div class="rounded-lg border border-zinc-200 p-4 text-center dark:border-zinc-700">
                            <flux:text class="text-sm text-zinc-500">
                                {{ __('Please') }}
                                <a href="{{ route('login') }}" class="font-medium text-zinc-900 underline dark:text-zinc-100">{{ __('log in') }}</a>
                                {{ __('to write a review.') }}
                            </flux:text>
                        </div>
                    @endauth
                </div>
            </div>
        </section>

        {{-- Related Products --}}
        @if($this->relatedProducts->isNotEmpty())
            <flux:separator class="my-10" />

            <section class="space-y-6">
                <flux:heading level="lg">{{ __('Related Products') }}</flux:heading>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($this->relatedProducts as $relatedProduct)
                        <div wire:key="related-{{ $relatedProduct->id }}" class="group">
                            <a href="{{ route('products.show', $relatedProduct->slug) }}" wire:navigate class="block">
                                <div class="relative aspect-square overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                    @if($relatedProduct->getFirstMediaUrl('thumbnail'))
                                        <img
                                            src="{{ $relatedProduct->getFirstMediaUrl('thumbnail') }}"
                                            alt="{{ $relatedProduct->name }}"
                                            class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        />
                                    @else
                                        <div class="flex size-full items-center justify-center">
                                            <flux:icon icon="photo" class="size-12 text-zinc-300 dark:text-zinc-600" />
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-3 space-y-1">
                                    @if($relatedProduct->brand)
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $relatedProduct->brand->name }}</flux:text>
                                    @endif
                                    <flux:text variant="strong" class="text-sm line-clamp-1">{{ $relatedProduct->name }}</flux:text>
                                    @php
                                        $minPrice = $relatedProduct->variants->map(fn ($v) => (float) $v->effective_price)->min();
                                    @endphp
                                    @if($minPrice !== null)
                                        <flux:text class="text-sm font-semibold">
                                            {{ __('From :price', ['price' => '$' . number_format($minPrice, 2)]) }}
                                        </flux:text>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
