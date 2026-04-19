<?php

use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Orders')]
class extends Component {
    use WithPagination;

    public ?string $filter_status = null;

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
        return Order::where('user_id', auth()->id())
            ->when($this->filter_status, fn ($q) => $q->where('status', $this->filter_status))
            ->with('items.variant.product')
            ->latest()
            ->paginate(10);
    }
}; ?>
<div>
    <div class="container mx-auto px-4 py-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('Home') }}</a>
            <flux:icon icon="chevron-right" class="size-3.5" />
            <span class="text-zinc-700 dark:text-zinc-300">{{ __('My Orders') }}</span>
        </nav>

        {{-- Header --}}
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading level="lg">{{ __('My Orders') }}</flux:heading>

            <flux:select wire:model.live="filter_status" :placeholder="__('All Statuses')" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="shipped">{{ __('Shipped') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
            </flux:select>
        </div>

        @if($this->orders->count() > 0)
            <flux:table :paginate="$this->orders">
                <flux:table.columns>
                    <flux:table.column>{{ __('Order #') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Items') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Payment') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('Total') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($this->orders as $order)
                        @php
                            $statusColors = [
                                'pending' => 'yellow',
                                'paid' => 'green',
                                'shipped' => 'blue',
                                'completed' => 'green',
                                'cancelled' => 'red',
                            ];
                            $paymentColors = [
                                'unpaid' => 'yellow',
                                'paid' => 'green',
                                'failed' => 'red',
                            ];
                        @endphp
                        <flux:table.row :key="$order->id">
                            <flux:table.cell variant="strong">{{ $order->order_number }}</flux:table.cell>
                            <flux:table.cell>{{ $order->created_at->format('M d, Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $order->items->count() }} {{ __('items') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ $statusColors[$order->status] ?? 'zinc' }}" size="sm">
                                    {{ ucfirst($order->status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ $paymentColors[$order->payment_status] ?? 'zinc' }}" size="sm">
                                    {{ ucfirst($order->payment_status) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">${{ number_format((float) $order->total, 2) }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button :href="route('account.orders.show', $order)" variant="ghost" size="sm" wire:navigate>
                                    {{ __('View') }}
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <flux:icon icon="shopping-bag" class="size-16 text-zinc-300 dark:text-zinc-600 mb-4" />
                <flux:heading level="lg" class="mb-2">{{ __('No orders yet') }}</flux:heading>
                <flux:text class="mb-6">{{ __('You haven\'t placed any orders yet. Start shopping to see your orders here.') }}</flux:text>
                <flux:button :href="route('products.index')" variant="primary" wire:navigate>
                    {{ __('Browse Products') }}
                </flux:button>
            </div>
        @endif
    </div>
</div>
