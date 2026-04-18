<?php

use App\Models\Category;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit Category')] class extends Component {
    public Category $category;

    public string $name = '';

    public ?int $parent_id = null;

    public bool $is_featured = false;

    public function mount(Category $category): void
    {
        $this->category = $category;
        $this->name = $category->name;
        $this->parent_id = $category->parent_id;
        $this->is_featured = $category->is_featured;
    }

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
        return Category::where('id', '!=', $this->category->id)
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $this->validate();

        $this->category->update([
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'featured_at' => $this->is_featured ? ($this->category->featured_at ?? now()) : null,
        ]);

        Flux::toast(variant: 'success', text: __('Category updated.'));
    }

    public function delete(): void
    {
        $this->category->delete();
        Flux::toast(variant: 'success', text: __('Category deleted.'));
        $this->redirect(route('admin.categories.index'), navigate: true);
    }
}; ?>
<x-layouts::admin :title="__('Edit Category')">
    <flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Edit Category') }}</flux:heading>

            <flux:modal.trigger name="delete-category">
                <flux:button variant="danger">{{ __('Delete') }}</flux:button>
            </flux:modal.trigger>

            <flux:modal name="delete-category" class="min-w-[22rem]">
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
                        <flux:button variant="danger" wire:click="delete">
                            {{ __('Delete category') }}
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
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
</x-layouts::admin>
