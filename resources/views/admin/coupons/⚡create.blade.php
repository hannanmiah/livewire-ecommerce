<?php

use App\Models\Coupon;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')]
#[Title('Create Coupon')]
class extends Component {
    public string $code = '';

    public string $type = 'fixed';

    public string $value = '';

    public ?string $min_order_amount = null;

    public ?string $usage_limit = null;

    public ?string $expires_at = null;

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => [
                'required',
                'numeric',
                'min:0',
                Rule::when($this->type === 'percent', ['max:100']),
            ],
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ];
    }

    public function save(): void
    {
        $this->code = strtoupper(trim($this->code));
        $this->validate();

        $coupon = Coupon::create([
            'code' => strtoupper($this->code),
            'type' => $this->type,
            'value' => $this->value,
            'min_order_amount' => $this->min_order_amount ?: null,
            'usage_limit' => $this->usage_limit ?: null,
            'expires_at' => $this->expires_at ?: null,
        ]);

        Flux::toast(variant: 'success', text: __('Coupon created.'));
        $this->redirect(route('admin.coupons.edit', $coupon), navigate: true);
    }
}; ?>
<flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Create Coupon') }}</flux:heading>
        </div>

        <form wire:submit="save" class="space-y-6 max-w-lg">
            <flux:input wire:model="code" :label="__('Code')" type="text" required autofocus />

            <flux:error name="code" />

            <flux:select wire:model="type" :label="__('Type')">
                <flux:select.option value="fixed">{{ __('Fixed') }}</flux:select.option>
                <flux:select.option value="percent">{{ __('Percent') }}</flux:select.option>
            </flux:select>

            <flux:error name="type" />

            <flux:input wire:model="value" :label="__('Value')" type="number" step="0.01" min="0" required />

            <flux:error name="value" />

            <flux:input wire:model="min_order_amount" :label="__('Minimum Order Amount')" type="number" step="0.01" min="0" :placeholder="__('Optional')" />

            <flux:error name="min_order_amount" />

            <flux:input wire:model="usage_limit" :label="__('Usage Limit')" type="number" min="1" :placeholder="__('Optional (unlimited)')" />

            <flux:error name="usage_limit" />

            <flux:input wire:model="expires_at" :label="__('Expires At')" type="datetime-local" />

            <flux:error name="expires_at" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

                <flux:button :href="route('admin.coupons.index')" variant="ghost" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </flux:main>
