<?php

use App\Models\Order;
use App\Services\OrderService;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Payment')] class extends Component {
    #[Locked]
    public Order $order;

    public string $payment_method = 'card';

    public function mount(Order $order): void
    {
        if ($order->user_id !== auth()->id()) {
            $this->redirect(route('home'), navigate: true);

            return;
        }

        $this->order = $order->load('items.variant.product', 'payment');

        if ($order->payment_status === 'paid') {
            $this->redirect(route('account.orders.show', $order), navigate: true);
        }
    }

    public function pay(OrderService $orderService): void
    {
        $this->validate([
            'payment_method' => 'required|in:card,bkash,bank,cash',
        ]);

        $success = $orderService->processPayment($this->order, $this->payment_method);

        if ($success) {
            Flux::toast(variant: 'success', text: __('Payment successful!'));
            $this->redirect(route('account.orders.show', $this->order), navigate: true);
        } else {
            Flux::toast(variant: 'danger', text: __('Payment failed. Please try again.'));
        }
    }

    public function cancelOrder(OrderService $orderService): void
    {
        $orderService->cancelOrder($this->order);
        Flux::toast(variant: 'warning', text: __('Order cancelled.'));
        $this->redirect(route('home'), navigate: true);
    }
}; ?>
<div>
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('Home') }}</a>
            <flux:icon icon="chevron-right" class="size-3.5" />
            <span class="text-zinc-700 dark:text-zinc-300">{{ __('Payment') }}</span>
        </nav>

        <flux:heading level="lg" class="mb-8">{{ __('Complete Payment') }}</flux:heading>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-5">
            {{-- Payment Form --}}
            <div class="lg:col-span-3">
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="lg" class="mb-6">{{ __('Payment Method') }}</flux:heading>

                    <div class="space-y-3">
                        {{-- Card --}}
                        <label
                            @class([
                                'flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-colors',
                                'border-zinc-900 bg-zinc-50 dark:border-zinc-100 dark:bg-zinc-700' => $payment_method === 'card',
                                'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600' => $payment_method !== 'card',
                            ])
                        >
                            <flux:radio wire:model.live="payment_method" value="card" name="payment_method" />
                            <div class="flex items-center gap-3">
                                <flux:icon icon="credit-card" class="size-5 text-zinc-500 dark:text-zinc-400" />
                                <div>
                                    <flux:text variant="strong">{{ __('Credit / Debit Card') }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pay with your card') }}</flux:text>
                                </div>
                            </div>
                        </label>

                        {{-- bKash --}}
                        <label
                            @class([
                                'flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-colors',
                                'border-zinc-900 bg-zinc-50 dark:border-zinc-100 dark:bg-zinc-700' => $payment_method === 'bkash',
                                'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600' => $payment_method !== 'bkash',
                            ])
                        >
                            <flux:radio wire:model.live="payment_method" value="bkash" name="payment_method" />
                            <div class="flex items-center gap-3">
                                <flux:icon icon="device-phone-mobile" class="size-5 text-zinc-500 dark:text-zinc-400" />
                                <div>
                                    <flux:text variant="strong">{{ __('bKash') }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pay with bKash mobile banking') }}</flux:text>
                                </div>
                            </div>
                        </label>

                        {{-- Bank Transfer --}}
                        <label
                            @class([
                                'flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-colors',
                                'border-zinc-900 bg-zinc-50 dark:border-zinc-100 dark:bg-zinc-700' => $payment_method === 'bank',
                                'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600' => $payment_method !== 'bank',
                            ])
                        >
                            <flux:radio wire:model.live="payment_method" value="bank" name="payment_method" />
                            <div class="flex items-center gap-3">
                                <flux:icon icon="building-library" class="size-5 text-zinc-500 dark:text-zinc-400" />
                                <div>
                                    <flux:text variant="strong">{{ __('Bank Transfer') }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pay via bank transfer') }}</flux:text>
                                </div>
                            </div>
                        </label>

                        {{-- Cash on Delivery --}}
                        <label
                            @class([
                                'flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-colors',
                                'border-zinc-900 bg-zinc-50 dark:border-zinc-100 dark:bg-zinc-700' => $payment_method === 'cash',
                                'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600' => $payment_method !== 'cash',
                            ])
                        >
                            <flux:radio wire:model.live="payment_method" value="cash" name="payment_method" />
                            <div class="flex items-center gap-3">
                                <flux:icon icon="banknotes" class="size-5 text-zinc-500 dark:text-zinc-400" />
                                <div>
                                    <flux:text variant="strong">{{ __('Cash on Delivery') }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Pay when you receive the order') }}</flux:text>
                                </div>
                            </div>
                        </label>
                    </div>

                    <flux:error name="payment_method" />

                    {{-- Simulated Card Form --}}
                    @if($payment_method === 'card')
                        <div class="mt-6 space-y-4 rounded-lg border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700">
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This is a simulated payment. No real card details are needed.') }}</flux:text>

                            <flux:field>
                                <flux:label>{{ __('Card Number') }}</flux:label>
                                <flux:input placeholder="4242 4242 4242 4242" disabled />
                            </flux:field>

                            <div class="grid grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label>{{ __('Expiry') }}</flux:label>
                                    <flux:input placeholder="12/28" disabled />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('CVC') }}</flux:label>
                                    <flux:input placeholder="123" disabled />
                                </flux:field>
                            </div>
                        </div>
                    @endif

                    {{-- Simulated bKash Form --}}
                    @if($payment_method === 'bkash')
                        <div class="mt-6 space-y-4 rounded-lg border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-700">
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This is a simulated payment. No real details are needed.') }}</flux:text>

                            <flux:field>
                                <flux:label>{{ __('bKash Number') }}</flux:label>
                                <flux:input placeholder="+880 1XXX-XXXXXX" disabled />
                            </flux:field>
                        </div>
                    @endif

                    <div class="mt-6 space-y-3">
                        <flux:button variant="primary" class="w-full" wire:click="pay" wire:loading.attr="disabled">
                            <flux:icon icon="lock-closed" class="size-4" />
                            {{ __('Pay $:amount', ['amount' => number_format((float) $order->total, 2)]) }}
                        </flux:button>

                        <flux:button variant="ghost" size="sm" class="w-full text-red-500 dark:text-red-400" wire:click="cancelOrder" wire:loading.attr="disabled">
                            {{ __('Cancel Order') }}
                        </flux:button>
                    </div>
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="lg:col-span-2">
                <div class="sticky top-8 rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:heading size="md" class="mb-4">{{ __('Order Summary') }}</flux:heading>

                    <div class="mb-4 flex items-center gap-2">
                        <flux:badge color="zinc" size="sm">{{ $order->order_number }}</flux:badge>
                    </div>

                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($order->items as $item)
                            <div wire:key="payment-item-{{ $item->id }}" class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="flex-1 min-w-0">
                                    <flux:text variant="strong" class="text-sm line-clamp-1">{{ $item->description }}</flux:text>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Qty: :quantity', ['quantity' => $item->quantity]) }}</flux:text>
                                </div>
                                <flux:text class="text-sm">${{ number_format((float) $item->total, 2) }}</flux:text>
                            </div>
                        @endforeach
                    </div>

                    <flux:separator class="my-4" />

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Subtotal') }}</flux:text>
                            <flux:text>${{ number_format((float) $order->subtotal, 2) }}</flux:text>
                        </div>

                        @if($order->discount > 0)
                            <div class="flex justify-between text-green-600 dark:text-green-400">
                                <flux:text>{{ __('Discount') }}</flux:text>
                                <flux:text>-${{ number_format((float) $order->discount, 2) }}</flux:text>
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Shipping') }}</flux:text>
                            <flux:text>{{ $order->shipping_fee > 0 ? '$' . number_format((float) $order->shipping_fee, 2) : __('Free') }}</flux:text>
                        </div>

                        <flux:separator />

                        <div class="flex justify-between text-base font-semibold">
                            <flux:text variant="strong">{{ __('Total') }}</flux:text>
                            <flux:text variant="strong">${{ number_format((float) $order->total, 2) }}</flux:text>
                        </div>
                    </div>

                    <flux:separator class="my-4" />

                    <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <flux:icon icon="shield-check" class="size-4" />
                        <span>{{ __('Your payment information is secure') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
