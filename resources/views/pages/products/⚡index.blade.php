<?php

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Products')]
class extends Component {
    use WithPagination;

    // Route parameters
    public ?Category $category = null;
    public ?Brand $brand = null;

    // Filters
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public array $selected_categories = [];

    #[Url]
    public array $selected_brands = [];

    #[Url]
    public array $selected_attributes = []; // ['Size' => ['M', 'L'], 'Color' => ['Red']]

    #[Url]
    public ?string $price_min = null;

    #[Url]
    public ?string $price_max = null;

    #[Url]
    public string $sort = 'newest';

    public bool $showMobileFilters = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategories(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedBrands(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedAttributes(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'selected_categories', 'selected_brands', 'selected_attributes', 'price_min', 'price_max']);
        $this->resetPage();
    }

    public function toggleMobileFilters(): void
    {
        $this->showMobileFilters = !$this->showMobileFilters;
    }

    public function removeBrand(string $brandId): void
    {
        $this->selected_brands = array_values(array_diff($this->selected_brands, [$brandId]));
        $this->resetPage();
    }

    public function removeCategory(string $categoryId): void
    {
        $this->selected_categories = array_values(array_diff($this->selected_categories, [$categoryId]));
        $this->resetPage();
    }

    public function removeAttribute(string $attributeName, string $value): void
    {
        if (isset($this->selected_attributes[$attributeName])) {
            $this->selected_attributes[$attributeName] = array_values(
                array_diff($this->selected_attributes[$attributeName], [$value])
            );
            if (empty($this->selected_attributes[$attributeName])) {
                unset($this->selected_attributes[$attributeName]);
            }
        }
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        $query = Product::query()
            ->with(['brand', 'media', 'variants' => fn($q) => $q->orderBy('price')])
            ->available()
            ->when($this->category || $this->selected_categories, function ($q) {
                $categoryIds = $this->selected_categories;
                if ($this->category) {
                    $categoryIds[] = $this->category->id;
                    $categoryIds = array_merge($categoryIds, $this->category->descendants()->pluck('id')->toArray());
                }
                $q->where(function ($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds)
                        ->orWhereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
                });
            })
            ->when($this->brand || $this->selected_brands, function ($q) {
                $brandIds = $this->selected_brands;
                if ($this->brand) {
                    $brandIds[] = $this->brand->id;
                }
                $q->whereIn('brand_id', $brandIds);
            })
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->selected_attributes, function ($q) {
                foreach ($this->selected_attributes as $attributeName => $values) {
                    if (!empty($values)) {
                        $q->whereHas('variants.attributeValues.attribute', function ($q) use ($attributeName) {
                            $q->where('name', $attributeName);
                        });
                        $q->whereHas('variants.attributeValues', function ($q) use ($values) {
                            $q->whereIn('value', $values);
                        });
                    }
                }
            })
            ->when($this->price_min || $this->price_max, function ($q) {
                $q->whereHas('variants', function ($q) {
                    $when_min = $this->price_min;
                    $when_max = $this->price_max;
                    $q->when($when_min, fn($q) => $q->where('price', '>=', $when_min));
                    $q->when($when_max, fn($q) => $q->where('price', '<=', $when_max));
                });
            });

        match ($this->sort) {
            'price_low' => $query->addSelect(['min_price' => ProductVariant::selectRaw('MIN(price)')->whereColumn('product_id', 'products.id')])->orderBy('min_price'),
            'price_high' => $query->addSelect(['min_price' => ProductVariant::selectRaw('MIN(price)')->whereColumn('product_id', 'products.id')])->orderByDesc('min_price'),
            default => $query->latest(),
        };

        return $query->paginate(12);
    }

    #[Computed]
    public function categories()
    {
        return Category::withCount('products')->orderBy('name')->get();
    }

    #[Computed]
    public function brands()
    {
        return Brand::withCount('products')->orderBy('name')->get();
    }

    #[Computed]
    public function filterAttributes()
    {
        return Attribute::with('values')->get();
    }

    public function hasActiveFilters(): bool
    {
        return !empty($this->search)
            || !empty($this->selected_categories)
            || !empty($this->selected_brands)
            || !empty($this->selected_attributes)
            || $this->price_min
            || $this->price_max;
    }
}; ?>
<div>
    <div class="container mx-auto px-4 py-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('home') }}" wire:navigate
               class="hover:text-zinc-700 dark:hover:text-zinc-200">{{ __('Home') }}</a>
            <flux:icon icon="chevron-right" class="inline size-3 mx-1"/>
            @if($category)
                <a href="{{ route('products.index') }}" wire:navigate
                   class="hover:text-zinc-700 dark:hover:text-zinc-200">{{ __('Products') }}</a>
                <flux:icon icon="chevron-right" class="inline size-3 mx-1"/>
                <span class="text-zinc-900 dark:text-zinc-100">{{ $category->name }}</span>
            @elseif($brand)
                <a href="{{ route('products.index') }}" wire:navigate
                   class="hover:text-zinc-700 dark:hover:text-zinc-200">{{ __('Products') }}</a>
                <flux:icon icon="chevron-right" class="inline size-3 mx-1"/>
                <span class="text-zinc-900 dark:text-zinc-100">{{ $brand->name }}</span>
            @else
                <span class="text-zinc-900 dark:text-zinc-100">{{ __('Products') }}</span>
            @endif
        </nav>

        {{-- Header --}}
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading level="lg">
                @if($category)
                    {{ $category->name }}
                @elseif($brand)
                    {{ $brand->name }}
                @else
                    {{ __('All Products') }}
                @endif
            </flux:heading>

            <div class="flex items-center gap-3">
                {{-- Search --}}
                <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search products...') }}"
                        icon="magnifying-glass"
                        size="sm"
                        class="w-full sm:w-64"
                />

                {{-- Sort --}}
                <flux:select wire:model.live="sort" size="sm" class="w-40 shrink-0">
                    <flux:select.option value="newest">{{ __('Newest') }}</flux:select.option>
                    <flux:select.option value="price_low">{{ __('Price: Low to High') }}</flux:select.option>
                    <flux:select.option value="price_high">{{ __('Price: High to Low') }}</flux:select.option>
                </flux:select>

                {{-- Mobile filter toggle --}}
                <flux:button
                        icon="funnel"
                        variant="outline"
                        size="sm"
                        class="lg:hidden shrink-0"
                        wire:click="toggleMobileFilters"
                />
            </div>
        </div>

        {{-- Active filters --}}
        @if($this->hasActiveFilters())
            <div class="flex flex-wrap items-center gap-2 mb-6">
                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Active filters:') }}</span>

                @if($search)
                    <flux:badge color="zinc" size="sm" inset="top bottom">
                        {{ __('Search: :q', ['q' => $search]) }}
                        <button wire:click="$set('search', '')"
                                class="ml-1 hover:text-zinc-700 dark:hover:text-zinc-200">&times;
                        </button>
                    </flux:badge>
                @endif

                @foreach($selected_brands as $brandId)
                    @php
                        $brandItem = $this->brands->firstWhere('id', $brandId);
                    @endphp
                    @if($brandItem)
                        <flux:badge color="zinc" size="sm" inset="top bottom">
                            {{ $brandItem->name }}
                            <button wire:click="removeBrand('{{ $brandId }}')"
                                    class="ml-1 hover:text-zinc-700 dark:hover:text-zinc-200">&times;
                            </button>
                        </flux:badge>
                    @endif
                @endforeach

                @foreach($selected_categories as $catId)
                    @php
                        $catItem = $this->categories->firstWhere('id', $catId);
                    @endphp
                    @if($catItem)
                        <flux:badge color="zinc" size="sm" inset="top bottom">
                            {{ $catItem->name }}
                            <button wire:click="removeCategory('{{ $catId }}')"
                                    class="ml-1 hover:text-zinc-700 dark:hover:text-zinc-200">&times;
                            </button>
                        </flux:badge>
                    @endif
                @endforeach

                @foreach($selected_attributes as $attrName => $values)
                    @foreach($values as $val)
                        <flux:badge color="zinc" size="sm" inset="top bottom">
                            {{ $attrName }}: {{ $val }}
                            <button wire:click="removeAttribute('{{ $attrName }}', '{{ $val }}')"
                                    class="ml-1 hover:text-zinc-700 dark:hover:text-zinc-200">&times;
                            </button>
                        </flux:badge>
                    @endforeach
                @endforeach

                @if($price_min || $price_max)
                    <flux:badge color="zinc" size="sm" inset="top bottom">
                        {{ __('Price: :min - :max', ['min' => $price_min ?: '*', 'max' => $price_max ?: '*']) }}
                        <button wire:click="$set('price_min', null); $set('price_max', null)"
                                class="ml-1 hover:text-zinc-700 dark:hover:text-zinc-200">&times;
                        </button>
                    </flux:badge>
                @endif

                <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                    {{ __('Clear all') }}
                </flux:button>
            </div>
        @endif

        <div class="flex gap-8">
            {{-- Sidebar Filters (Desktop) --}}
            <aside class="hidden lg:block w-64 shrink-0">
                <div class="sticky top-8 space-y-6">
                    @include('pages.products._filters')
                </div>
            </aside>

            {{-- Mobile Filters (Slide-down) --}}
            @if($showMobileFilters)
                <div class="lg:hidden fixed inset-0 z-50 bg-black/50" wire:click="toggleMobileFilters">
                    <div class="absolute right-0 top-0 h-full w-80 max-w-full bg-white dark:bg-zinc-800 shadow-xl overflow-y-auto"
                         wire:click.stop>
                        <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:heading>{{ __('Filters') }}</flux:heading>
                            <flux:button icon="x-mark" variant="ghost" size="sm" wire:click="toggleMobileFilters"/>
                        </div>
                        <div class="p-4 space-y-6">
                            @include('pages.products._filters')
                        </div>
                        <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button variant="primary" class="w-full" wire:click="toggleMobileFilters">
                                {{ __('Apply Filters') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Product Grid --}}
            <div class="flex-1 min-w-0">
                @if($this->products->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                        @foreach($this->products as $product)
                            <a
                                    href="{{ route('products.show', $product->slug) }}"
                                    wire:navigate
                                    wire:key="product-{{ $product->id }}"
                                    class="group block rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800 overflow-hidden hover:shadow-lg transition-shadow"
                            >
                                {{-- Thumbnail --}}
                                <div class="aspect-square bg-zinc-100 dark:bg-zinc-700 overflow-hidden">
                                    @if($product->getFirstMediaUrl('thumbnail'))
                                        <img
                                                src="{{ $product->getFirstMediaUrl('thumbnail') }}"
                                                alt="{{ $product->name }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        />
                                    @else
                                        <div class="flex items-center justify-center w-full h-full text-zinc-400">
                                            <flux:icon icon="photo" class="size-12"/>
                                        </div>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="p-3 md:p-4">
                                    @if($product->brand)
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">{{ $product->brand->name }}</p>
                                    @endif

                                    <h3 class="text-sm font-medium text-zinc-900 dark:text-zinc-100 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $product->name }}
                                    </h3>

                                    @php
                                        $variants = $product->variants;
                                        $hasSale = $variants->contains(fn ($v) => $v->is_on_sale);
                                        $minPrice = $variants->min('price');
                                        $maxPrice = $variants->max('price');
                                        $minSalePrice = $variants->whereNotNull('sale_price')->min('sale_price');
                                    @endphp

                                    <div class="mt-2">
                                        @if($hasSale && $minSalePrice)
                                            <span class="text-xs text-zinc-400 line-through">{{ number_format((float) $minPrice, 2) }}</span>
                                            <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ __('From :price', ['price' => number_format((float) $minSalePrice, 2)]) }}</span>
                                        @else
                                            <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                                @if($minPrice == $maxPrice)
                                                    {{ number_format((float) $minPrice, 2) }}
                                                @else
                                                    {{ __('From :price', ['price' => number_format((float) $minPrice, 2)]) }}
                                                @endif
                                            </span>
                                        @endif
                                    </div>

                                    @if($product->is_featured)
                                        <flux:badge color="amber" size="sm"
                                                    class="mt-2">{{ __('Featured') }}</flux:badge>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $this->products->links() }}
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-20 text-center">
                        <flux:icon icon="magnifying-glass" class="size-16 text-zinc-300 dark:text-zinc-600 mb-4"/>
                        <flux:heading level="lg" class="mb-2">{{ __('No products found') }}</flux:heading>
                        <flux:text class="mb-6">{{ __('Try adjusting your filters or search terms.') }}</flux:text>
                        @if($this->hasActiveFilters())
                            <flux:button variant="primary" wire:click="clearFilters">
                                {{ __('Clear all filters') }}
                            </flux:button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
