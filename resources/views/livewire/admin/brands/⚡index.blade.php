<?php

use App\Models\Brand;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

new #[Title('Brands')] class extends Component {
    use WithPagination;

    public string $search = '';
    public ?string $filter_featured = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterFeatured(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function brands()
    {
        return Brand::when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filter_featured === 'yes', fn ($q) => $q->whereNotNull('featured_at')->where('featured_at', '<=', now()))
            ->when($this->filter_featured === 'no', fn ($q) => $q->where(fn ($q) => $q->whereNull('featured_at')->orWhere('featured_at', '>', now())))
            ->orderBy('name')
            ->paginate(10);
    }

    public function delete(int $brandId): void
    {
        $brand = Brand::findOrFail($brandId);
        $brand->delete();
        Flux::toast(variant: 'success', text: __('Brand deleted.'));
        unset($this->brands);
    }
};

?>

<x-layouts::admin :title="__('Brands')">
    <flux:main>
        <div class="flex items-center justify-between">
            <flux:heading level="lg">{{ __('Brands') }}</flux:heading>
            <flux:button :href="route('admin.brands.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('Create Brand') }}
            </flux:button>
        </div>

        <div class="flex items-center gap-4 mt-6">
            <flux:input wire:model.live.debounce="search" :placeholder="__('Search brands...')" icon="magnifying-glass" class="w-full max-w-sm" />

            <flux:select wire:model.live="filter_featured" :placeholder="__('All')" class="w-40">
                <flux:select.option value="yes">{{ __('Featured') }}</flux:select.option>
                <flux:select.option value="no">{{ __('Not Featured') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table :paginate="$this->brands" class="mt-6">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Slug') }}</flux:table.column>
                <flux:table.column>{{ __('Featured') }}</flux:table.column>
                <flux:table.column>{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->brands as $brand)
                    <flux:table.row :key="$brand->id">
                        <flux:table.cell variant="strong">{{ $brand->name }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500">{{ $brand->slug }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($brand->is_featured)
                                <flux:badge color="green" size="sm">{{ __('Featured') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button :href="route('admin.brands.edit', $brand)" variant="ghost" size="sm" icon="pencil-square" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>

                                <flux:modal.trigger name="delete-brand-{{ $brand->id }}">
                                    <flux:button variant="ghost" size="sm" icon="trash">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </flux:modal.trigger>

                                <flux:modal name="delete-brand-{{ $brand->id }}" class="md:w-96">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg">{{ __('Delete Brand') }}</flux:heading>
                                            <flux:text class="mt-2">
                                                {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $brand->name]) }}
                                            </flux:text>
                                        </div>
                                        <div class="flex gap-3">
                                            <flux:spacer />
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>
                                            <flux:button wire:click="delete({{ $brand->id }})" variant="danger">{{ __('Delete') }}</flux:button>
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
