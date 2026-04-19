<?php

use App\Models\Order;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Order Success')]
class extends Component {
    #[Locked]
    public Order $order;

    public function mount(Order $order): void
    {
        if ($order->user_id !== auth()->id()) {
            $this->redirect(route('home'), navigate: true);

            return;
        }
        $this->order = $order->load('items.variant.product');
    }
}; ?>

<div>
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center text-center">
            <div class="flex size-20 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                <flux:icon icon="check" class="size-10 text-green-600 dark:text-green-400" variant="micro" />
            </div>

            <flux:heading level="lg" class="mt-6">{{ __('Thank you for your order!') }}</flux:heading>
            <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                {{ __('Your order :number has been placed and is being processed.', ['number' => $order->order_number]) }}
            </flux:text>

            <div class="mt-10 w-full max-w-2xl rounded-lg border border-zinc-200 bg-white p-6 text-left dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="md" class="mb-4">{{ __('Order Summary') }}</flux:heading>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach($order->items as $item)
                        <div wire:key="success-item-{{ $item->id }}" class="flex items-center gap-4 py-4 first:pt-0 last:pb-0">
                            <div class="size-16 flex-shrink-0 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                @if($item->variant->product->getFirstMediaUrl('thumbnail'))
                                    <img src="{{ $item->variant->product->getFirstMediaUrl('thumbnail') }}" alt="{{ $item->variant->product->name }}" class="size-full object-cover" />
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <flux:icon icon="photo" class="size-6 text-zinc-300 dark:text-zinc-600" />
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <flux:text variant="strong" class="line-clamp-1">{{ $item->variant->product->name }}</flux:text>
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Qty: :quantity', ['quantity' => $item->quantity]) }}</flux:text>
                            </div>

                            <flux:text variant="strong">${{ number_format((float) $item->total, 2) }}</flux:text>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 space-y-2 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                    <div class="flex justify-between text-sm">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Subtotal') }}</flux:text>
                        <flux:text>${{ number_format((float) $order->subtotal, 2) }}</flux:text>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-sm">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Discount') }}</flux:text>
                            <flux:text class="text-green-600 dark:text-green-400">-${{ number_format((float) $order->discount, 2) }}</flux:text>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Shipping') }}</flux:text>
                        <flux:text>{{ $order->shipping_fee > 0 ? '$' . number_format((float) $order->shipping_fee, 2) : __('Free') }}</flux:text>
                    </div>
                    <div class="flex justify-between border-t border-zinc-200 pt-2 font-semibold dark:border-zinc-700">
                        <flux:text variant="strong">{{ __('Total') }}</flux:text>
                        <flux:text variant="strong" class="text-lg">${{ number_format((float) $order->total, 2) }}</flux:text>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <flux:button variant="primary" :href="route('account.orders.show', $order)" wire:navigate>
                    {{ __('View Full Details') }}
                </flux:button>
                <flux:button variant="outline" :href="route('home')" wire:navigate>
                    {{ __('Continue Shopping') }}
                </flux:button>
            </div>
        </div>
    </div>
</div>
