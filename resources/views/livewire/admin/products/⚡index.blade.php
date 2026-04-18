<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Products')] class extends Component {
    use WithPagination;

    public string $search = '';
    public ?int $filter_category = null;
    public ?int $filter_brand = null;
    public ?string $filter_featured = null;
    public ?string $filter_available = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterBrand(): void
    {
        $this->resetPage();
    }

    public function updatedFilterFeatured(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAvailable(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        return Product::with(['category', 'brand', 'variants' => fn ($q) => $q->orderBy('price')])
            ->withMin('variants', 'price')
            ->withMax('variants', 'price')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filter_category, fn ($q) => $q->where('category_id', $this->filter_category))
            ->when($this->filter_brand, fn ($q) => $q->where('brand_id', $this->filter_brand))
            ->when($this->filter_featured === 'yes', fn ($q) => $q->featured())
            ->when($this->filter_featured === 'no', fn ($q) => $q->where(fn ($q) => $q->whereNull('featured_at')->orWhere('featured_at', '>', now())))
            ->when($this->filter_available === 'yes', fn ($q) => $q->available())
            ->when($this->filter_available === 'no', fn ($q) => $q->where(fn ($q) => $q->whereNull('available_at')->orWhere('available_at', '>', now())))
            ->latest()
            ->paginate(12);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function brands()
    {
        return Brand::orderBy('name')->get();
    }

    public function delete(int $productId): void
    {
        Product::findOrFail($productId)->delete();
        Flux::toast(variant: 'success', text: __('Product deleted.'));
        unset($this->products);
    }
}; ?>

<x-layouts::admin :title="__('Products')">
    <flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Products') }}</flux:heading>

            <flux:button :href="route('admin.products.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('Create Product') }}
            </flux:button>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row mb-6">
            <flux:input wire:model.live.debounce="search" :placeholder="__('Search products...')" icon="magnifying-glass" class="sm:max-w-sm" />

            <flux:select wire:model.live="filter_category" :placeholder="__('All Categories')" class="sm:max-w-xs">
                @foreach ($this->categories as $category)
                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filter_brand" :placeholder="__('All Brands')" class="sm:max-w-xs">
                @foreach ($this->brands as $brand)
                    <flux:select.option value="{{ $brand->id }}">{{ $brand->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filter_featured" :placeholder="__('Featured')" class="sm:max-w-[10rem]">
                <flux:select.option value="">{{ __('All') }}</flux:select.option>
                <flux:select.option value="yes">{{ __('Featured') }}</flux:select.option>
                <flux:select.option value="no">{{ __('Not Featured') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filter_available" :placeholder="__('Availability')" class="sm:max-w-[10rem]">
                <flux:select.option value="">{{ __('All') }}</flux:select.option>
                <flux:select.option value="yes">{{ __('Available') }}</flux:select.option>
                <flux:select.option value="no">{{ __('Unavailable') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table :paginate="$this->products" class="mt-6">
            <flux:table.columns>
                <flux:table.column>{{ __('Thumbnail') }}</flux:table.column>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Brand') }}</flux:table.column>
                <flux:table.column>{{ __('Price Range') }}</flux:table.column>
                <flux:table.column>{{ __('Featured') }}</flux:table.column>
                <flux:table.column>{{ __('Available') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->products as $product)
                    <flux:table.row :key="$product->id">
                        <flux:table.cell>
                            @if ($product->getFirstMediaUrl('thumbnail'))
                                <img src="{{ $product->getFirstMediaUrl('thumbnail') }}" alt="{{ $product->name }}" class="h-10 w-10 rounded object-cover" />
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-700">
                                    <flux:icon icon="photo" class="text-zinc-400 size-5" />
                                </div>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell variant="strong">{{ $product->name }}</flux:table.cell>
                        <flux:table.cell>{{ $product->category?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $product->brand?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($product->variants_min_price !== null)
                                {{ __('From :price', ['price' => number_format((float) $product->variants_min_price, 2)]) }}
                            @else
                                —
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($product->is_featured)
                                <flux:badge color="green" size="sm" inset="top bottom">{{ __('Featured') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm" inset="top bottom">{{ __('No') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($product->is_available)
                                <flux:badge color="blue" size="sm" inset="top bottom">{{ __('Available') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm" inset="top bottom">{{ __('No') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button :href="route('admin.products.edit', $product)" variant="ghost" size="sm" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>

                                <flux:modal.trigger name="delete-product-{{ $product->id }}">
                                    <flux:button variant="ghost" size="sm" color="danger">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </flux:modal.trigger>

                                <flux:modal name="delete-product-{{ $product->id }}" class="min-w-[22rem]">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg">{{ __('Delete product?') }}</flux:heading>
                                            <flux:text class="mt-2">
                                                {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $product->name]) }}
                                            </flux:text>
                                        </div>
                                        <div class="flex gap-2">
                                            <flux:spacer />
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>
                                            <flux:button variant="danger" wire:click="delete({{ $product->id }})">
                                                {{ __('Delete product') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </flux:modal>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:main>
</x-layouts::admin>
