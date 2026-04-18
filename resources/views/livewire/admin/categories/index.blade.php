<?php

use App\Models\Category;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Categories')] class extends Component {
    public string $search = '';

    public ?string $filter_featured = null;

    #[Computed]
    public function categories()
    {
        return Category::with('parent')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filter_featured === 'yes', fn ($q) => $q->whereNotNull('featured_at')->where('featured_at', '<=', now()))
            ->when($this->filter_featured === 'no', fn ($q) => $q->where(fn ($q) => $q->whereNull('featured_at')->orWhere('featured_at', '>', now())))
            ->orderBy('name')
            ->paginate(10);
    }

    public function delete(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $category->delete();
        Flux::toast(variant: 'success', text: __('Category deleted.'));
        unset($this->categories);
    }
}; ?>
<x-layouts::admin :title="__('Categories')">
    <flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Categories') }}</flux:heading>

            <flux:button :href="route('admin.categories.create')" variant="primary" wire:navigate>
                {{ __('Create Category') }}
            </flux:button>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row mb-6">
            <flux:input wire:model.live.debounce="search" :placeholder="__('Search categories...')" icon="magnifying-glass" class="sm:max-w-sm" />

            <flux:select wire:model.live="filter_featured" :placeholder="__('Filter by featured')" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All') }}</flux:select.option>
                <flux:select.option value="yes">{{ __('Featured') }}</flux:select.option>
                <flux:select.option value="no">{{ __('Not Featured') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table :paginate="$this->categories">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Slug') }}</flux:table.column>
                <flux:table.column>{{ __('Parent Category') }}</flux:table.column>
                <flux:table.column>{{ __('Featured') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->categories as $category)
                    <flux:table.row :key="$category->id">
                        <flux:table.cell variant="strong">{{ $category->name }}</flux:table.cell>
                        <flux:table.cell>{{ $category->slug }}</flux:table.cell>
                        <flux:table.cell>{{ $category->parent?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($category->is_featured)
                                <flux:badge color="green" size="sm" inset="top bottom">{{ __('Featured') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm" inset="top bottom">{{ __('No') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button :href="route('admin.categories.edit', $category)" variant="ghost" size="sm" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>

                                <flux:modal.trigger name="delete-category-{{ $category->id }}">
                                    <flux:button variant="ghost" size="sm" color="danger">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </flux:modal.trigger>

                                <flux:modal name="delete-category-{{ $category->id }}" class="min-w-[22rem]">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg">{{ __('Delete category?') }}</flux:heading>
                                            <flux:text class="mt-2">
                                                {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $category->name]) }}
                                            </flux:text>
                                        </div>
                                        <div class="flex gap-2">
                                            <flux:spacer />
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>
                                            <flux:button variant="danger" wire:click="delete({{ $category->id }})">
                                                {{ __('Delete category') }}
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
