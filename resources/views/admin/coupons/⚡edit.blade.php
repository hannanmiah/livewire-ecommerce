<?php

use App\Models\Coupon;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')]
#[Title('Edit Coupon')]
class extends Component {
    public Coupon $coupon;

    public string $code = '';

    public string $type = 'fixed';

    public string $value = '';

    public ?string $min_order_amount = null;

    public ?string $usage_limit = null;

    public ?string $expires_at = null;

    public function mount(Coupon $coupon): void
    {
        $this->coupon = $coupon;
        $this->code = $coupon->code;
        $this->type = $coupon->type;
        $this->value = (string) $coupon->value;
        $this->min_order_amount = $coupon->min_order_amount ? (string) $coupon->min_order_amount : null;
        $this->usage_limit = $coupon->usage_limit ? (string) $coupon->usage_limit : null;
        $this->expires_at = $coupon->expires_at?->format('Y-m-d\TH:i');
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:coupons,code,' . $this->coupon->id,
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->coupon->update([
            'code' => strtoupper($this->code),
            'type' => $this->type,
            'value' => $this->value,
            'min_order_amount' => $this->min_order_amount ?: null,
            'usage_limit' => $this->usage_limit ?: null,
            'expires_at' => $this->expires_at ?: null,
        ]);

        Flux::toast(variant: 'success', text: __('Coupon updated.'));
    }

    public function delete(): void
    {
        $this->coupon->delete();
        Flux::toast(variant: 'success', text: __('Coupon deleted.'));
        $this->redirect(route('admin.coupons.index'), navigate: true);
    }
}; ?>
<flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Edit Coupon') }}</flux:heading>

            <flux:button :href="route('admin.coupons.index')" variant="ghost" wire:navigate>
                {{ __('Back to Coupons') }}
            </flux:button>
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

            <flux:input :value="$coupon->used_count" :label="__('Times Used')" type="text" disabled />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>

                <flux:button :href="route('admin.coupons.index')" variant="ghost" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>

                <flux:spacer />

                <flux:modal.trigger name="delete-coupon">
                    <flux:button variant="ghost" color="danger">
                        {{ __('Delete') }}
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal name="delete-coupon" class="min-w-[22rem]">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">{{ __('Delete coupon?') }}</flux:heading>
                            <flux:text class="mt-2">
                                {{ __('Are you sure you want to delete coupon :code? This action cannot be undone.', ['code' => $coupon->code]) }}
                            </flux:text>
                        </div>
                        <div class="flex gap-2">
                            <flux:spacer />
                            <flux:modal.close>
                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:button variant="danger" wire:click="delete">
                                {{ __('Delete coupon') }}
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>
            </div>
        </form>
    </flux:main>
