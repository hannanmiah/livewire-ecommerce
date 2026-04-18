<?php

use App\Models\Order;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Order Details')] class extends Component {
    public Order $order;

    public string $status = '';

    public string $shipping_status = '';

    public function mount(Order $order): void
    {
        $this->order = $order->load('items.variant.product', 'user', 'payment', 'coupon');
        $this->status = $order->status;
        $this->shipping_status = $order->shipping_status;
    }

    public function updateStatus(): void
    {
        $this->validate(['status' => 'required|in:pending,paid,shipped,completed,cancelled']);
        $this->order->update(['status' => $this->status]);
        Flux::toast(variant: 'success', text: __('Order status updated.'));
    }

    public function updateShippingStatus(): void
    {
        $this->validate(['shipping_status' => 'required|in:pending,shipped,delivered']);
        $this->order->update(['shipping_status' => $this->shipping_status]);
        Flux::toast(variant: 'success', text: __('Shipping status updated.'));
    }
}; ?>
<x-layouts::admin :title="__('Order :number', ['number' => $order->order_number])">
    <flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Order :number', ['number' => $order->order_number]) }}</flux:heading>

            <flux:button :href="route('admin.orders.index')" variant="ghost" wire:navigate>
                {{ __('Back to Orders') }}
            </flux:button>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Order Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Order Info --}}
                <flux:card>
                    <flux:heading size="md" class="mb-4">{{ __('Order Information') }}</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:text class="text-zinc-500">{{ __('Order Number') }}</flux:text>
                            <flux:text>{{ $order->order_number }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-zinc-500">{{ __('Date') }}</flux:text>
                            <flux:text>{{ $order->created_at->format('M d, Y H:i') }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-zinc-500">{{ __('Status') }}</flux:text>
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
                        </div>
                        <div>
                            <flux:text class="text-zinc-500">{{ __('Payment Status') }}</flux:text>
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
                        </div>
                        <div>
                            <flux:text class="text-zinc-500">{{ __('Shipping Status') }}</flux:text>
                            @php
                                $shippingColors = [
                                    'pending' => 'yellow',
                                    'shipped' => 'blue',
                                    'delivered' => 'green',
                                ];
                            @endphp
                            <flux:badge color="{{ $shippingColors[$order->shipping_status] ?? 'zinc' }}" size="sm">
                                {{ ucfirst($order->shipping_status) }}
                            </flux:badge>
                        </div>
                    </div>
                </flux:card>

                {{-- Order Items --}}
                <flux:card>
                    <flux:heading size="md" class="mb-4">{{ __('Order Items') }}</flux:heading>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Product') }}</flux:table.column>
                            <flux:table.column>{{ __('Price') }}</flux:table.column>
                            <flux:table.column>{{ __('Qty') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('Total') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($order->items as $item)
                                <flux:table.row :key="$item->id">
                                    <flux:table.cell variant="strong">
                                        {{ $item->variant->product->name }}
                                        @if ($item->description)
                                            <flux:text class="text-zinc-500">{{ $item->description }}</flux:text>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $item->price }}</flux:table.cell>
                                    <flux:table.cell>{{ $item->quantity }}</flux:table.cell>
                                    <flux:table.cell align="end">{{ $item->total }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>

                    <div class="mt-4 space-y-2 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                        <div class="flex justify-between">
                            <flux:text>{{ __('Subtotal') }}</flux:text>
                            <flux:text>{{ $order->subtotal }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text>{{ __('Shipping') }}</flux:text>
                            <flux:text>{{ $order->shipping_fee }}</flux:text>
                        </div>
                        @if ($order->discount > 0)
                            <div class="flex justify-between">
                                <flux:text>{{ __('Discount') }}</flux:text>
                                <flux:text class="text-green-600">-{{ $order->discount }}</flux:text>
                            </div>
                        @endif
                        <div class="flex justify-between font-semibold border-t border-zinc-200 dark:border-zinc-700 pt-2">
                            <flux:text>{{ __('Total') }}</flux:text>
                            <flux:text>{{ $order->total }}</flux:text>
                        </div>
                    </div>
                </flux:card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Customer Info --}}
                <flux:card>
                    <flux:heading size="md" class="mb-4">{{ __('Customer') }}</flux:heading>

                    <div class="space-y-2">
                        <div>
                            <flux:text class="text-zinc-500">{{ __('Name') }}</flux:text>
                            <flux:text>{{ $order->user->name }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-zinc-500">{{ __('Email') }}</flux:text>
                            <flux:text>{{ $order->user->email }}</flux:text>
                        </div>
                    </div>
                </flux:card>

                {{-- Payment Info --}}
                @if ($order->payment)
                    <flux:card>
                        <flux:heading size="md" class="mb-4">{{ __('Payment') }}</flux:heading>

                        <div class="space-y-2">
                            <div>
                                <flux:text class="text-zinc-500">{{ __('Method') }}</flux:text>
                                <flux:text>{{ ucfirst($order->payment->method) }}</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-zinc-500">{{ __('Amount') }}</flux:text>
                                <flux:text>{{ $order->payment->amount }}</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-zinc-500">{{ __('Status') }}</flux:text>
                                @php
                                    $payColors = ['pending' => 'yellow', 'success' => 'green', 'failed' => 'red'];
                                @endphp
                                <flux:badge color="{{ $payColors[$order->payment->status] ?? 'zinc' }}" size="sm">
                                    {{ ucfirst($order->payment->status) }}
                                </flux:badge>
                            </div>
                            @if ($order->payment->transaction_id)
                                <div>
                                    <flux:text class="text-zinc-500">{{ __('Transaction ID') }}</flux:text>
                                    <flux:text>{{ $order->payment->transaction_id }}</flux:text>
                                </div>
                            @endif
                            @if ($order->payment->paid_at)
                                <div>
                                    <flux:text class="text-zinc-500">{{ __('Paid At') }}</flux:text>
                                    <flux:text>{{ $order->payment->paid_at->format('M d, Y H:i') }}</flux:text>
                                </div>
                            @endif
                        </div>
                    </flux:card>
                @endif

                {{-- Coupon Info --}}
                @if ($order->coupon)
                    <flux:card>
                        <flux:heading size="md" class="mb-4">{{ __('Coupon') }}</flux:heading>

                        <div class="space-y-2">
                            <div>
                                <flux:text class="text-zinc-500">{{ __('Code') }}</flux:text>
                                <flux:text>{{ $order->coupon->code }}</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-zinc-500">{{ __('Type') }}</flux:text>
                                <flux:text>{{ ucfirst($order->coupon->type) }}</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-zinc-500">{{ __('Value') }}</flux:text>
                                <flux:text>{{ $order->coupon->type === 'percent' ? $order->coupon->value . '%' : $order->coupon->value }}</flux:text>
                            </div>
                        </div>
                    </flux:card>
                @endif

                {{-- Update Status --}}
                <flux:card>
                    <flux:heading size="md" class="mb-4">{{ __('Update Status') }}</flux:heading>

                    <form wire:submit="updateStatus" class="space-y-4">
                        <flux:select wire:model="status" :label="__('Order Status')">
                            <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                            <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                            <flux:select.option value="shipped">{{ __('Shipped') }}</flux:select.option>
                            <flux:select.option value="completed">{{ __('Completed') }}</flux:select.option>
                            <flux:select.option value="cancelled">{{ __('Cancelled') }}</flux:select.option>
                        </flux:select>

                        <flux:error name="status" />

                        <flux:button variant="primary" type="submit" class="w-full">{{ __('Update Order Status') }}</flux:button>
                    </form>

                    <flux:separator class="my-4" />

                    <form wire:submit="updateShippingStatus" class="space-y-4">
                        <flux:select wire:model="shipping_status" :label="__('Shipping Status')">
                            <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                            <flux:select.option value="shipped">{{ __('Shipped') }}</flux:select.option>
                            <flux:select.option value="delivered">{{ __('Delivered') }}</flux:select.option>
                        </flux:select>

                        <flux:error name="shipping_status" />

                        <flux:button variant="primary" type="submit" class="w-full">{{ __('Update Shipping Status') }}</flux:button>
                    </form>
                </flux:card>
            </div>
        </div>
    </flux:main>
</x-layouts::admin>
