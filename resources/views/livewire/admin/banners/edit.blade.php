<?php

use App\Models\Banner;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Edit Banner')] class extends Component {
    use WithFileUploads;

    public Banner $banner;

    public string $title = '';

    public string $category = 'home';

    public ?string $position = null;

    public ?string $link = null;

    public ?string $description = null;

    public bool $is_featured = false;

    public $image = null;

    public function mount(Banner $banner): void
    {
        $this->banner = $banner;
        $this->title = $banner->title;
        $this->category = $banner->category;
        $this->position = $banner->position;
        $this->link = $banner->link;
        $this->description = $banner->description;
        $this->is_featured = $banner->is_featured;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category' => 'required|in:home,sidebar,product',
            'position' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->banner->update([
            'title' => $this->title,
            'category' => $this->category,
            'position' => $this->position,
            'link' => $this->link,
            'description' => $this->description,
            'featured_at' => $this->is_featured ? now() : null,
        ]);

        if ($this->image) {
            $this->banner->clearMediaCollection('image');
            $this->banner->addMedia($this->image->getRealPath())
                ->usingName($this->image->getClientOriginalName())
                ->toMediaCollection('image');
        }

        Flux::toast(variant: 'success', text: __('Banner updated.'));
    }

    public function delete(): void
    {
        $this->banner->delete();
        Flux::toast(variant: 'success', text: __('Banner deleted.'));
        $this->redirect(route('admin.banners.index'), navigate: true);
    }
}; ?>
<x-layouts::admin :title="__('Edit Banner')">
    <flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Edit Banner') }}</flux:heading>

            <flux:button :href="route('admin.banners.index')" variant="ghost" wire:navigate>
                {{ __('Back to Banners') }}
            </flux:button>
        </div>

        <form wire:submit="save" class="space-y-6 max-w-lg">
            <flux:input wire:model="title" :label="__('Title')" type="text" required autofocus />

            <flux:error name="title" />

            <flux:select wire:model="category" :label="__('Category')">
                <flux:select.option value="home">{{ __('Home') }}</flux:select.option>
                <flux:select.option value="sidebar">{{ __('Sidebar') }}</flux:select.option>
                <flux:select.option value="product">{{ __('Product') }}</flux:select.option>
            </flux:select>

            <flux:error name="category" />

            <flux:select wire:model="position" :label="__('Position')" :placeholder="__('None')">
                <flux:select.option value="hero">{{ __('Hero') }}</flux:select.option>
                <flux:select.option value="home_top">{{ __('Home Top') }}</flux:select.option>
                <flux:select.option value="home_middle">{{ __('Home Middle') }}</flux:select.option>
                <flux:select.option value="home_bottom">{{ __('Home Bottom') }}</flux:select.option>
                <flux:select.option value="sidebar_top">{{ __('Sidebar Top') }}</flux:select.option>
                <flux:select.option value="sidebar_bottom">{{ __('Sidebar Bottom') }}</flux:select.option>
            </flux:select>

            <flux:error name="position" />

            <flux:input wire:model="link" :label="__('Link')" type="text" :placeholder="__('Optional')" />

            <flux:error name="link" />

            <flux:textarea wire:model="description" :label="__('Description')" :placeholder="__('Optional')" />

            <flux:error name="description" />

            <flux:checkbox wire:model="is_featured" :label="__('Featured')" />

            <div>
                <flux:text class="mb-2">{{ __('Image') }}</flux:text>

                @if ($banner->getFirstMediaUrl('image'))
                    <div class="mb-2">
                        <img src="{{ $banner->getFirstMediaUrl('image') }}" alt="{{ $banner->title }}" class="h-24 w-40 rounded object-cover" />
                    </div>
                @endif

                <input type="file" wire:model="image" accept="image/*" class="block w-full text-sm text-zinc-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-zinc-100 dark:file:bg-zinc-700
                    file:text-zinc-700 dark:file:text-zinc-300
                    hover:file:bg-zinc-200 dark:hover:file:bg-zinc-600" />
                <flux:error name="image" />

                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="mt-2 h-24 w-40 rounded object-cover" />
                @endif
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

                <flux:button :href="route('admin.banners.index')" variant="ghost" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>

                <flux:spacer />

                <flux:modal.trigger name="delete-banner">
                    <flux:button variant="ghost" color="danger">
                        {{ __('Delete') }}
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal name="delete-banner" class="min-w-[22rem]">
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
                            <flux:button variant="danger" wire:click="delete">
                                {{ __('Delete banner') }}
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>
            </div>
        </form>
    </flux:main>
</x-layouts::admin>
