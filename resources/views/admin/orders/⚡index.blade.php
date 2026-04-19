<?php

use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin')]
#[Title('Orders')]
class extends Component {
    use WithPagination;

    public string $search = '';

    public ?string $filter_status = null;

    public ?string $filter_payment_status = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPaymentStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
        return Order::with('user', 'payment')
            ->when($this->search, fn ($q) => $q->where('order_number', 'like', "%{$this->search}%"))
            ->when($this->filter_status, fn ($q) => $q->where('status', $this->filter_status))
            ->when($this->filter_payment_status, fn ($q) => $q->where('payment_status', $this->filter_payment_status))
            ->latest()
            ->paginate(15);
    }
}; ?>
<flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Orders') }}</flux:heading>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row mb-6">
            <flux:input wire:model.live.debounce="search" :placeholder="__('Search by order number...')" icon="magnifying-glass" class="sm:max-w-sm" />

            <flux:select wire:model.live="filter_status" :placeholder="__('All Statuses')" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="shipped">{{ __('Shipped') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filter_payment_status" :placeholder="__('All Payment Statuses')" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All Payment Statuses') }}</flux:select.option>
                <flux:select.option value="unpaid">{{ __('Unpaid') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="failed">{{ __('Failed') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table :paginate="$this->orders">
            <flux:table.columns>
                <flux:table.column>{{ __('Order #') }}</flux:table.column>
                <flux:table.column>{{ __('Customer') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Payment') }}</flux:table.column>
                <flux:table.column>{{ __('Total') }}</flux:table.column>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->orders as $order)
                    <flux:table.row :key="$order->id">
                        <flux:table.cell variant="strong">{{ $order->order_number }}</flux:table.cell>
                        <flux:table.cell>{{ $order->user->name }}</flux:table.cell>
                        <flux:table.cell>
                            @php
                                $statusColors = [
                                    'pending' => 'yellow',
                                    'paid' => 'green',
                                    'shipped' => 'blue',
                                    'completed' => 'green',
                                    'cancelled' => 'red',
                                ];
                            @endphp
                            <flux:badge color="{{ $statusColors[$order->status] ?? 'zinc' }}" size="sm">
                                {{ ucfirst($order->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @php
                                $paymentColors = [
                                    'unpaid' => 'yellow',
                                    'paid' => 'green',
                                    'failed' => 'red',
                                ];
                            @endphp
                            <flux:badge color="{{ $paymentColors[$order->payment_status] ?? 'zinc' }}" size="sm">
                                {{ ucfirst($order->payment_status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $order->total }}</flux:table.cell>
                        <flux:table.cell>{{ $order->created_at->format('M d, Y') }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button :href="route('admin.orders.show', $order)" variant="ghost" size="sm" wire:navigate>
                                {{ __('View') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:main>
