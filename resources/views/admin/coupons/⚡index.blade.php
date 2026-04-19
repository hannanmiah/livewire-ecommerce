<?php

use App\Models\Coupon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

new #[Layout('layouts::admin')]
#[Title('Coupons')] class extends Component {
    use WithPagination;

    public string $search = '';

    public ?string $filter_type = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function coupons()
    {
        return Coupon::when($this->search, fn ($q) => $q->where('code', 'like', "%{$this->search}%"))
            ->when($this->filter_type, fn ($q) => $q->where('type', $this->filter_type))
            ->latest()
            ->paginate(10);
    }

    public function delete(int $couponId): void
    {
        $coupon = Coupon::findOrFail($couponId);
        $coupon->delete();
        Flux::toast(variant: 'success', text: __('Coupon deleted.'));
        unset($this->coupons);
    }
}; ?>
<flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Coupons') }}</flux:heading>

            <flux:button :href="route('admin.coupons.create')" variant="primary" wire:navigate>
                {{ __('Create Coupon') }}
            </flux:button>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row mb-6">
            <flux:input wire:model.live.debounce="search" :placeholder="__('Search by code...')" icon="magnifying-glass" class="sm:max-w-sm" />

            <flux:select wire:model.live="filter_type" :placeholder="__('All Types')" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                <flux:select.option value="fixed">{{ __('Fixed') }}</flux:select.option>
                <flux:select.option value="percent">{{ __('Percent') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table :paginate="$this->coupons">
            <flux:table.columns>
                <flux:table.column>{{ __('Code') }}</flux:table.column>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column>{{ __('Value') }}</flux:table.column>
                <flux:table.column>{{ __('Min Order') }}</flux:table.column>
                <flux:table.column>{{ __('Usage') }}</flux:table.column>
                <flux:table.column>{{ __('Expires') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->coupons as $coupon)
                    <flux:table.row :key="$coupon->id">
                        <flux:table.cell variant="strong">{{ $coupon->code }}</flux:table.cell>
                        <flux:table.cell>{{ ucfirst($coupon->type) }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($coupon->type === 'percent')
                                {{ $coupon->value }}%
                            @else
                                {{ $coupon->value }}
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $coupon->min_order_amount ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '&' }}infin;</flux:table.cell>
                        <flux:table.cell>{{ $coupon->expires_at?->format('M d, Y') ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($coupon->isValid())
                                <flux:badge color="green" size="sm">{{ __('Valid') }}</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">{{ __('Expired') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button :href="route('admin.coupons.edit', $coupon)" variant="ghost" size="sm" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>

                                <flux:modal.trigger name="delete-coupon-{{ $coupon->id }}">
                                    <flux:button variant="ghost" size="sm" color="danger">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </flux:modal.trigger>

                                <flux:modal name="delete-coupon-{{ $coupon->id }}" class="min-w-[22rem]">
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
                                            <flux:button variant="danger" wire:click="delete({{ $coupon->id }})">
                                                {{ __('Delete coupon') }}
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
</flux:main>
