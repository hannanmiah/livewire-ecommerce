<?php

use App\Models\Brand;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Edit Brand')] class extends Component {
    public Brand $brand;
    public string $name = '';
    public bool $is_featured = false;

    public function mount(Brand $brand): void
    {
        $this->brand = $brand;
        $this->name = $brand->name;
        $this->is_featured = $brand->is_featured;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->brand->update([
            'name' => $this->name,
            'featured_at' => $this->is_featured ? ($this->brand->featured_at ?? now()) : null,
        ]);

        Flux::toast(variant: 'success', text: __('Brand updated.'));
    }

    public function delete(): void
    {
        $this->brand->delete();
        Flux::toast(variant: 'success', text: __('Brand deleted.'));
        $this->redirect(route('admin.brands.index'), navigate: true);
    }
};

?>
<x-layouts::admin :title="__('Edit Brand')">
    <flux:main>
        <div class="flex items-center gap-4">
            <flux:heading level="lg">{{ __('Edit Brand') }}</flux:heading>
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

                <flux:spacer />

                <flux:modal.trigger name="delete-brand">
                    <flux:button variant="danger" icon="trash">{{ __('Delete') }}</flux:button>
                </flux:modal.trigger>
            </div>
        </form>

        <flux:modal name="delete-brand" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Delete Brand') }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $this->brand->name]) }}
                    </flux:text>
                </div>
                <div class="flex gap-3">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button wire:click="delete" variant="danger">{{ __('Delete') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    </flux:main>
</x-layouts::admin>
