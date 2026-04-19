<?php

use App\Models\Order;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Order Details')]
class extends Component {
    #[Locked]
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load('items.variant.product', 'payment', 'coupon');
    }
}; ?>
<div> $order->order_number])">
    <div class="container mx-auto px-4 py-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('Home') }}</a>
            <flux:icon icon="chevron-right" class="size-3.5" />
            <a href="{{ route('account.orders') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('My Orders') }}</a>
            <flux:icon icon="chevron-right" class="size-3.5" />
            <span class="text-zinc-700 dark:text-zinc-300">{{ $order->order_number }}</span>
        </nav>

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <flux:heading level="lg">{{ __('Order :number', ['number' => $order->order_number]) }}</flux:heading>

            <flux:button :href="route('account.orders')" variant="ghost" wire:navigate icon="arrow-left">
                {{ __('Back to Orders') }}
            </flux:button>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Order Info --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="md" class="mb-4">{{ __('Order Information') }}</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Order Number') }}</flux:text>
                            <flux:text variant="strong">{{ $order->order_number }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</flux:text>
                            <flux:text>{{ $order->created_at->format('M d, Y H:i') }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</flux:text>
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
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Payment Status') }}</flux:text>
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
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Shipping Status') }}</flux:text>
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
                </div>

                {{-- Order Items --}}
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="md" class="mb-4">{{ __('Order Items') }}</flux:heading>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Product') }}</flux:table.column>
                            <flux:table.column>{{ __('Price') }}</flux:table.column>
                            <flux:table.column>{{ __('Qty') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('Total') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach($order->items as $item)
                                <flux:table.row :key="$item->id">
                                    <flux:table.cell variant="strong">
                                        @if($item->variant && $item->variant->product)
                                            <a href="{{ route('products.show', $item->variant->product->slug) }}" wire:navigate class="hover:text-blue-600 dark:hover:text-blue-400">
                                                {{ $item->variant->product->name }}
                                            </a>
                                        @else
                                            {{ $item->description ?? __('N/A') }}
                                        @endif
                                        @if($item->description)
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->description }}</flux:text>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>${{ number_format((float) $item->price, 2) }}</flux:table.cell>
                                    <flux:table.cell>{{ $item->quantity }}</flux:table.cell>
                                    <flux:table.cell align="end">${{ number_format((float) $item->total, 2) }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>

                    {{-- Totals --}}
                    <div class="mt-4 space-y-2 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                        <div class="flex justify-between">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Subtotal') }}</flux:text>
                            <flux:text>${{ number_format((float) $order->subtotal, 2) }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Shipping') }}</flux:text>
                            <flux:text>${{ number_format((float) $order->shipping_fee, 2) }}</flux:text>
                        </div>
                        @if($order->discount > 0)
                            <div class="flex justify-between">
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Discount') }}</flux:text>
                                <flux:text class="text-green-600 dark:text-green-400">-${{ number_format((float) $order->discount, 2) }}</flux:text>
                            </div>
                        @endif
                        <div class="flex justify-between font-semibold border-t border-zinc-200 dark:border-zinc-700 pt-2">
                            <flux:text variant="strong">{{ __('Total') }}</flux:text>
                            <flux:text variant="strong">${{ number_format((float) $order->total, 2) }}</flux:text>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Payment Info --}}
                @if($order->payment)
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="md" class="mb-4">{{ __('Payment') }}</flux:heading>

                        <div class="space-y-3">
                            <div>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Method') }}</flux:text>
                                <flux:text>{{ ucfirst($order->payment->method) }}</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</flux:text>
                                <flux:text>${{ number_format((float) $order->payment->amount, 2) }}</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</flux:text>
                                @php
                                    $payColors = ['pending' => 'yellow', 'success' => 'green', 'failed' => 'red'];
                                @endphp
                                <flux:badge color="{{ $payColors[$order->payment->status] ?? 'zinc' }}" size="sm">
                                    {{ ucfirst($order->payment->status) }}
                                </flux:badge>
                            </div>
                            @if($order->payment->transaction_id)
                                <div>
                                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Transaction ID') }}</flux:text>
                                    <flux:text>{{ $order->payment->transaction_id }}</flux:text>
                                </div>
                            @endif
                            @if($order->payment->paid_at)
                                <div>
                                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Paid At') }}</flux:text>
                                    <flux:text>{{ $order->payment->paid_at->format('M d, Y H:i') }}</flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Coupon Info --}}
                @if($order->coupon)
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="md" class="mb-4">{{ __('Coupon') }}</flux:heading>

                        <div class="space-y-3">
                            <div>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Code') }}</flux:text>
                                <flux:text variant="strong">{{ $order->coupon->code }}</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</flux:text>
                                <flux:text>{{ ucfirst($order->coupon->type) }}</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Value') }}</flux:text>
                                <flux:text>{{ $order->coupon->type === 'percent' ? $order->coupon->value . '%' : '$' . number_format((float) $order->coupon->value, 2) }}</flux:text>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
