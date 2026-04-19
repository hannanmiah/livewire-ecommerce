<?php

use App\Models\Banner;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::admin')]
#[Title('Create Banner')] class extends Component {
    use WithFileUploads;

    public string $title = '';

    public string $category = 'home';

    public ?string $position = null;

    public ?string $link = null;

    public ?string $description = null;

    public bool $is_featured = false;

    public $image = null;

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category' => 'required|in:home,sidebar,product',
            'position' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'boolean',
            'image' => 'required|image|max:2048',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $banner = Banner::create([
            'title' => $this->title,
            'category' => $this->category,
            'position' => $this->position,
            'link' => $this->link,
            'description' => $this->description,
            'featured_at' => $this->is_featured ? now() : null,
        ]);

        if ($this->image) {
            $banner->addMedia($this->image->getRealPath())
                ->usingName($this->image->getClientOriginalName())
                ->toMediaCollection('image');
        }

        Flux::toast(variant: 'success', text: __('Banner created.'));
        $this->redirect(route('admin.banners.edit', $banner), navigate: true);
    }
}; ?>
<flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Create Banner') }}</flux:heading>
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
            </div>
        </form>
    </flux:main>
</flux:main>
