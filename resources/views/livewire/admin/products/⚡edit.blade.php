<?php

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Edit Product')] class extends Component {
    use WithFileUploads;

    #[Locked]
    public Product $product;

    public string $name = '';
    public string $description = '';
    public ?int $category_id = null;
    public ?int $brand_id = null;
    public array $additional_category_ids = [];
    public bool $is_featured = false;
    public bool $is_available = true;

    public $thumbnail = null;
    public array $gallery = [];

    public array $variants = [];

    public array $removed_gallery_ids = [];

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'additional_category_ids' => 'array',
            'additional_category_ids.*' => 'exists:categories,id',
            'thumbnail' => 'nullable|image|max:5120',
            'gallery' => 'array',
            'gallery.*' => 'image|max:5120',
            'variants' => 'required|array|min:1',
            'variants.*.sku' => 'required|string',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.stock_quantity' => 'required|integer|min:0',
            'variants.*.attribute_value_ids' => 'array',
            'variants.*.attribute_value_ids.*' => 'exists:attribute_values,id',
        ];
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

    #[Computed]
    public function attributes()
    {
        return Attribute::with('values')->get();
    }

    public function mount(Product $product): void
    {
        $product->load(['variants.stock', 'variants.attributeValues', 'categories']);

        $this->product = $product;
        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->category_id = $product->category_id;
        $this->brand_id = $product->brand_id;
        $this->additional_category_ids = $product->categories()->pluck('categories.id')->map(fn ($id) => (string) $id)->toArray();
        $this->is_featured = $product->is_featured;
        $this->is_available = $product->is_available;

        $this->variants = $product->variants->map(fn ($v) => [
            'id' => $v->id,
            'sku' => $v->sku,
            'price' => (string) $v->price,
            'sale_price' => (string) ($v->sale_price ?? ''),
            'weight' => (string) ($v->weight ?? ''),
            'stock_quantity' => $v->stock?->quantity ?? 0,
            'attribute_value_ids' => $v->attributeValues->pluck('id')->map(fn ($id) => (string) $id)->toArray(),
        ])->toArray();

        if (empty($this->variants)) {
            $this->addVariant();
        }
    }

    public function addVariant(): void
    {
        $this->variants[] = [
            'id' => null,
            'sku' => '',
            'price' => '',
            'sale_price' => '',
            'weight' => '',
            'stock_quantity' => 10,
            'attribute_value_ids' => [],
        ];
    }

    public function removeVariant(int $index): void
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    public function removeGalleryImage(int $index): void
    {
        unset($this->gallery[$index]);
        $this->gallery = array_values($this->gallery);
    }

    public function removeExistingGalleryImage(string $mediaId): void
    {
        $this->product->getMedia('gallery')->find($mediaId)?->delete();
        unset($this->product);
    }

    public function removeExistingThumbnail(): void
    {
        $this->product->clearMediaCollection('thumbnail');
        unset($this->product);
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $this->product->update([
                'name' => $this->name,
                'description' => $this->description,
                'category_id' => $this->category_id,
                'brand_id' => $this->brand_id,
                'featured_at' => $this->is_featured ? ($this->product->featured_at ?? now()) : null,
                'available_at' => $this->is_available ? ($this->product->available_at ?? now()) : null,
            ]);

            $this->product->categories()->sync($this->additional_category_ids);

            if ($this->thumbnail) {
                $this->product->clearMediaCollection('thumbnail');
                $this->product->addMedia($this->thumbnail->getRealPath())
                    ->usingName($this->thumbnail->getClientOriginalName())
                    ->toMediaCollection('thumbnail');
            }

            foreach ($this->gallery as $image) {
                $this->product->addMedia($image->getRealPath())
                    ->usingName($image->getClientOriginalName())
                    ->toMediaCollection('gallery');
            }

            $existingVariantIds = [];

            foreach ($this->variants as $variantData) {
                if (isset($variantData['id']) && $variantData['id']) {
                    $variant = ProductVariant::find($variantData['id']);
                    if ($variant) {
                        $variant->update([
                            'sku' => $variantData['sku'],
                            'price' => $variantData['price'],
                            'sale_price' => $variantData['sale_price'] ?: null,
                            'weight' => $variantData['weight'] ?: null,
                        ]);

                        if ($variant->stock) {
                            $variant->stock->update([
                                'quantity' => $variantData['stock_quantity'] ?? 0,
                            ]);
                        } else {
                            $variant->stock()->create([
                                'quantity' => $variantData['stock_quantity'] ?? 0,
                                'reserved_quantity' => 0,
                            ]);
                        }

                        $variant->attributeValues()->sync($variantData['attribute_value_ids']);
                        $existingVariantIds[] = $variant->id;
                    }
                } else {
                    $variant = $this->product->variants()->create([
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'sale_price' => $variantData['sale_price'] ?: null,
                        'weight' => $variantData['weight'] ?: null,
                    ]);

                    if (! empty($variantData['attribute_value_ids'])) {
                        $variant->attributeValues()->attach($variantData['attribute_value_ids']);
                    }

                    $variant->stock()->create([
                        'quantity' => $variantData['stock_quantity'] ?? 0,
                        'reserved_quantity' => 0,
                    ]);

                    $existingVariantIds[] = $variant->id;
                }
            }

            $this->product->variants()->whereNotIn('id', $existingVariantIds)->delete();
        });

        Flux::toast(variant: 'success', text: __('Product updated.'));
        $this->redirect(route('admin.products.index'), navigate: true);
    }

    public function delete(): void
    {
        $this->product->delete();
        Flux::toast(variant: 'success', text: __('Product deleted.'));
        $this->redirect(route('admin.products.index'), navigate: true);
    }
}; ?>

<x-layouts::admin :title="__('Edit Product')">
    <flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Edit Product') }}</flux:heading>

            <flux:modal.trigger name="delete-product">
                <flux:button variant="ghost" color="danger" icon="trash">
                    {{ __('Delete') }}
                </flux:button>
            </flux:modal.trigger>

            <flux:modal name="delete-product" class="min-w-[22rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Delete product?') }}</flux:heading>
                        <flux:text class="mt-2">
                            {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $this->product->name]) }}
                        </flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                        </flux:modal.close>
                        <flux:button variant="danger" wire:click="delete">
                            {{ __('Delete product') }}
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        </div>

        <form wire:submit="save" class="space-y-8 max-w-4xl">
            {{-- Section 1: Basic Info --}}
            <div class="space-y-6">
                <flux:heading level="md">{{ __('Basic Information') }}</flux:heading>

                <flux:field>
                    <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:textarea wire:model="description" :label="__('Description')" :placeholder="__('Product description...')" rows="4" />
                    <flux:error name="description" />
                </flux:field>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <flux:field>
                        <flux:select wire:model="category_id" :label="__('Primary Category')" :placeholder="__('Select category')">
                            @foreach ($this->categories as $category)
                                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="category_id" />
                    </flux:field>

                    <flux:field>
                        <flux:select wire:model="brand_id" :label="__('Brand')" :placeholder="__('Select brand')" required>
                            @foreach ($this->brands as $brand)
                                <flux:select.option value="{{ $brand->id }}">{{ $brand->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="brand_id" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Additional Categories') }}</flux:label>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
                        @foreach ($this->categories as $category)
                            <flux:field variant="inline">
                                <flux:checkbox wire:model="additional_category_ids" value="{{ $category->id }}" />
                                <flux:label>{{ $category->name }}</flux:label>
                            </flux:field>
                        @endforeach
                    </div>
                    <flux:error name="additional_category_ids" />
                </flux:field>

                <div class="flex gap-6">
                    <flux:field variant="inline">
                        <flux:checkbox wire:model="is_featured" />
                        <flux:label>{{ __('Featured') }}</flux:label>
                    </flux:field>

                    <flux:field variant="inline">
                        <flux:checkbox wire:model="is_available" />
                        <flux:label>{{ __('Available') }}</flux:label>
                    </flux:field>
                </div>
            </div>

            <flux:separator />

            {{-- Section 2: Images --}}
            <div class="space-y-6">
                <flux:heading level="md">{{ __('Images') }}</flux:heading>

                <flux:field>
                    <flux:label>{{ __('Thumbnail') }}</flux:label>
                    @if ($this->product->getFirstMediaUrl('thumbnail'))
                        <div class="mb-2 flex items-center gap-2">
                            <img src="{{ $this->product->getFirstMediaUrl('thumbnail') }}" alt="{{ $this->product->name }}" class="h-24 w-24 rounded object-cover" />
                            <flux:button type="button" wire:click="removeExistingThumbnail" variant="ghost" size="sm" color="danger" icon="trash" />
                        </div>
                    @endif
                    <input type="file" wire:model="thumbnail" accept="image/*" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-zinc-100 file:text-zinc-700 dark:file:bg-zinc-700 dark:file:text-zinc-300 hover:file:bg-zinc-200 dark:hover:file:bg-zinc-600" />
                    <flux:error name="thumbnail" />

                    @if ($thumbnail)
                        <div class="mt-2">
                            <img src="{{ $thumbnail->temporaryUrl() }}" alt="Thumbnail preview" class="h-24 w-24 rounded object-cover" />
                        </div>
                    @endif
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Gallery Images') }}</flux:label>
                    @if ($this->product->getMedia('gallery')->count())
                        <div class="flex flex-wrap gap-2 mb-2">
                            @foreach ($this->product->getMedia('gallery') as $media)
                                <div class="relative">
                                    <img src="{{ $media->getUrl() }}" alt="Gallery image" class="h-24 w-24 rounded object-cover" />
                                    <button type="button" wire:click="removeExistingGalleryImage('{{ $media->id }}')" class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white text-xs hover:bg-red-600">
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <input type="file" wire:model="gallery" accept="image/*" multiple class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-zinc-100 file:text-zinc-700 dark:file:bg-zinc-700 dark:file:text-zinc-300 hover:file:bg-zinc-200 dark:hover:file:bg-zinc-600" />
                    <flux:error name="gallery" />

                    @if (count($gallery))
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach ($gallery as $index => $image)
                                <div class="relative" wire:key="gallery-{{ $index }}">
                                    <img src="{{ $image->temporaryUrl() }}" alt="Gallery preview" class="h-24 w-24 rounded object-cover" />
                                    <button type="button" wire:click="removeGalleryImage({{ $index }})" class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white text-xs hover:bg-red-600">
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </flux:field>
            </div>

            <flux:separator />

            {{-- Section 3: Variants --}}
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <flux:heading level="md">{{ __('Variants') }}</flux:heading>
                    <flux:button type="button" wire:click="addVariant" variant="outline" size="sm" icon="plus">
                        {{ __('Add Variant') }}
                    </flux:button>
                </div>

                <flux:error name="variants" />

                @foreach ($this->variants as $index => $variant)
                    <div wire:key="variant-{{ $index }}" class="space-y-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <flux:text variant="strong">{{ __('Variant :number', ['number' => $index + 1]) }}</flux:text>
                            @if (count($this->variants) > 1)
                                <flux:button type="button" wire:click="removeVariant({{ $index }})" variant="ghost" size="sm" color="danger" icon="trash">
                                    {{ __('Remove') }}
                                </flux:button>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <flux:field>
                                <flux:input wire:model="variants.{{ $index }}.sku" :label="__('SKU')" placeholder="SKU-001" required />
                                <flux:error name="variants.{{ $index }}.sku" />
                            </flux:field>

                            <flux:field>
                                <flux:input wire:model="variants.{{ $index }}.price" :label="__('Price')" type="number" step="0.01" min="0" required />
                                <flux:error name="variants.{{ $index }}.price" />
                            </flux:field>

                            <flux:field>
                                <flux:input wire:model="variants.{{ $index }}.sale_price" :label="__('Sale Price')" type="number" step="0.01" min="0" />
                                <flux:error name="variants.{{ $index }}.sale_price" />
                            </flux:field>

                            <flux:field>
                                <flux:input wire:model="variants.{{ $index }}.weight" :label="__('Weight (kg)')" type="number" step="0.01" min="0" />
                                <flux:error name="variants.{{ $index }}.weight" />
                            </flux:field>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <flux:field>
                                <flux:input wire:model="variants.{{ $index }}.stock_quantity" :label="__('Stock Quantity')" type="number" min="0" required />
                                <flux:error name="variants.{{ $index }}.stock_quantity" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('Attributes') }}</flux:label>
                                <div class="space-y-2">
                                    @foreach ($this->attributes as $attribute)
                                        <div>
                                            <flux:text class="text-xs font-medium mb-1">{{ $attribute->name }}</flux:text>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($attribute->values as $value)
                                                    <flux:field variant="inline" class="text-sm">
                                                        <flux:checkbox wire:model="variants.{{ $index }}.attribute_value_ids" value="{{ $value->id }}" />
                                                        <flux:label>{{ $value->value }}</flux:label>
                                                    </flux:field>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <flux:error name="variants.{{ $index }}.attribute_value_ids" />
                            </flux:field>
                        </div>
                    </div>
                @endforeach
            </div>

            <flux:separator />

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Update Product') }}</flux:button>

                <flux:button :href="route('admin.products.index')" variant="ghost" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::admin>
