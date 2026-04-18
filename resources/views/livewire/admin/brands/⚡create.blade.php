<?php

use App\Models\Brand;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Create Brand')] class extends Component {
    public string $name = '';
    public bool $is_featured = false;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $brand = Brand::create([
            'name' => $this->name,
            'featured_at' => $this->is_featured ? now() : null,
        ]);

        Flux::toast(variant: 'success', text: __('Brand created.'));
        $this->redirect(route('admin.brands.edit', $brand), navigate: true);
    }
};

?>
<x-layouts::admin :title="__('Create Brand')">
    <flux:main>
        <div class="flex items-center gap-4">
            <flux:heading level="lg">{{ __('Create Brand') }}</flux:heading>
        </div>

        <form wire:submit="save" class="mt-6 space-y-6 max-w-lg">
            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input wire:model="name" :placeholder="__('Brand name')" />
                <flux:error name="name" />
            </flux:field>

            <flux:field variant="inline">
                <flux:label>{{ __('Featured') }}</flux:label>
                <flux:switch wire:model.live="is_featured" />
            </flux:field>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:button :href="route('admin.brands.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:main>
</x-layouts::admin>
