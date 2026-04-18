<?php

use App\Models\Banner;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

new #[Title('Banners')] class extends Component {
    use WithPagination;

    public ?string $filter_category = null;

    public ?string $filter_position = null;

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPosition(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function banners()
    {
        return Banner::with('media')
            ->when($this->filter_category, fn ($q) => $q->where('category', $this->filter_category))
            ->when($this->filter_position, fn ($q) => $q->where('position', $this->filter_position))
            ->latest()
            ->paginate(10);
    }

    public function delete(int $bannerId): void
    {
        $banner = Banner::findOrFail($bannerId);
        $banner->delete();
        Flux::toast(variant: 'success', text: __('Banner deleted.'));
        unset($this->banners);
    }
}; ?>

<x-layouts::admin :title="__('Banners')">
    <flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Banners') }}</flux:heading>

            <flux:button :href="route('admin.banners.create')" variant="primary" wire:navigate>
                {{ __('Create Banner') }}
            </flux:button>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row mb-6">
            <flux:select wire:model.live="filter_category" :placeholder="__('All Categories')" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All Categories') }}</flux:select.option>
                <flux:select.option value="home">{{ __('Home') }}</flux:select.option>
                <flux:select.option value="sidebar">{{ __('Sidebar') }}</flux:select.option>
                <flux:select.option value="product">{{ __('Product') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filter_position" :placeholder="__('All Positions')" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All Positions') }}</flux:select.option>
                <flux:select.option value="hero">{{ __('Hero') }}</flux:select.option>
                <flux:select.option value="home_top">{{ __('Home Top') }}</flux:select.option>
                <flux:select.option value="home_middle">{{ __('Home Middle') }}</flux:select.option>
                <flux:select.option value="home_bottom">{{ __('Home Bottom') }}</flux:select.option>
                <flux:select.option value="sidebar_top">{{ __('Sidebar Top') }}</flux:select.option>
                <flux:select.option value="sidebar_bottom">{{ __('Sidebar Bottom') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table :paginate="$this->banners">
            <flux:table.columns>
                <flux:table.column>{{ __('Image') }}</flux:table.column>
                <flux:table.column>{{ __('Title') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Position') }}</flux:table.column>
                <flux:table.column>{{ __('Featured') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->banners as $banner)
                    <flux:table.row :key="$banner->id">
                        <flux:table.cell>
                            @if ($banner->getFirstMediaUrl('image'))
                                <img src="{{ $banner->getFirstMediaUrl('image') }}" alt="{{ $banner->title }}" class="h-10 w-16 rounded object-cover" />
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell variant="strong">{{ $banner->title }}</flux:table.cell>
                        <flux:table.cell>{{ ucfirst($banner->category) }}</flux:table.cell>
                        <flux:table.cell>{{ $banner->position ? str_replace('_', ' ', ucfirst($banner->position)) : '—' }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($banner->is_featured)
                                <flux:badge color="green" size="sm">{{ __('Featured') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('No') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button :href="route('admin.banners.edit', $banner)" variant="ghost" size="sm" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>

                                <flux:modal.trigger name="delete-banner-{{ $banner->id }}">
                                    <flux:button variant="ghost" size="sm" color="danger">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </flux:modal.trigger>

                                <flux:modal name="delete-banner-{{ $banner->id }}" class="min-w-[22rem]">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg">{{ __('Delete banner?') }}</flux:heading>
                                            <flux:text class="mt-2">
                                                {{ __('Are you sure you want to delete :title? This action cannot be undone.', ['title' => $banner->title]) }}
                                            </flux:text>
                                        </div>
                                        <div class="flex gap-2">
                                            <flux:spacer />
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>
                                            <flux:button variant="danger" wire:click="delete({{ $banner->id }})">
                                                {{ __('Delete banner') }}
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
