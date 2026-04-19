<?php

use App\Models\Category;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')]
#[Title('Create Category')] class extends Component {
    public string $name = '';

    public ?int $parent_id = null;

    public bool $is_featured = false;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }

    #[Computed]
    public function parentCategories()
    {
        return Category::orderBy('name')->get();
    }

    public function save(): void
    {
        $this->validate();

        $category = Category::create([
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'featured_at' => $this->is_featured ? now() : null,
        ]);

        Flux::toast(variant: 'success', text: __('Category created.'));
        $this->redirect(route('admin.categories.edit', $category), navigate: true);
    }
}; ?>
<flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Create Category') }}</flux:heading>
        </div>

        <form wire:submit="save" class="space-y-6 max-w-lg">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus />

            <flux:error name="name" />

            <flux:select wire:model="parent_id" :label="__('Parent Category')" :placeholder="__('None (top-level)')">
                @foreach ($this->parentCategories as $parentCategory)
                    <flux:select.option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:error name="parent_id" />

            <flux:checkbox wire:model="is_featured" :label="__('Featured')" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

                <flux:button :href="route('admin.categories.index')" variant="ghost" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </flux:main>
</flux:main>
