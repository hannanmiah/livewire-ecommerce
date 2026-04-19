<?php

use App\Services\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    private CartService $cartService;

    public function boot(CartService $cartService): void
    {
        $this->cartService = $cartService;
    }

    #[On('cart-updated')]
    public function refresh(): void
    {
        // This will trigger a re-render
    }

    #[Computed]
    public function cartData(): array
    {
        $cart = $this->cartService->getCartForContext();

        return [
            'count' => $cart ? $cart->items()->count() : 0,
            'items' => $cart ? $cart->items()->with('variant.product')->latest()->take(5)->get() : collect(),
            'total' => $cart ? (float) $cart->total : 0,
        ];
    }
}; ?>

<div class="flex items-center">
    <flux:dropdown position="bottom" align="end">
        <flux:navbar.item class="!h-10 relative" icon="shopping-cart" :label="__('Cart')">
            @if($this->cartData['count'] > 0)
                <span class="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-zinc-900 text-xs font-bold text-white dark:bg-zinc-100 dark:text-zinc-900">
                    {{ $this->cartData['count'] }}
                </span>
            @endif
        </flux:navbar.item>

        <flux:menu class="w-80 p-0">
            <div class="px-4 py-3">
                <flux:heading size="sm">{{ __('Cart') }} ({{ $this->cartData['count'] }})</flux:heading>
            </div>

            <flux:menu.separator />

            @if($this->cartData['items']->isNotEmpty())
                <div class="max-h-64 overflow-y-auto">
                    @foreach($this->cartData['items'] as $item)
                        @php $itemProduct = $item->variant->product; @endphp
                        <div class="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800">
                            <div class="size-12 flex-shrink-0 overflow-hidden rounded-md border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                @if($itemProduct->getFirstMediaUrl('thumbnail'))
                                    <img src="{{ $itemProduct->getFirstMediaUrl('thumbnail') }}" alt="{{ $itemProduct->name }}" class="size-full object-cover" />
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <flux:icon icon="photo" class="size-4 text-zinc-300 dark:text-zinc-600" variant="micro" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <flux:text class="truncate text-sm font-medium">{{ $itemProduct->name }}</flux:text>
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Qty: :quantity x $:price', ['quantity' => $item->quantity, 'price' => number_format((float) $item->price, 2)]) }}
                                </flux:text>
                            </div>
                            <flux:text class="text-sm font-medium">${{ number_format((float) $item->total, 2) }}</flux:text>
                        </div>
                    @endforeach
                </div>

                <flux:menu.separator />

                <div class="flex items-center justify-between px-4 py-3">
                    <flux:text variant="strong" size="sm">{{ __('Subtotal') }}</flux:text>
                    <flux:text variant="strong" size="sm">${{ number_format($this->cartData['total'], 2) }}</flux:text>
                </div>

                <flux:menu.separator />

                <div class="space-y-2 p-4">
                    <flux:button variant="primary" :href="route('cart')" wire:navigate class="w-full" size="sm">
                        {{ __('View Cart') }}
                    </flux:button>
                    <flux:button variant="outline" :href="route('checkout')" wire:navigate class="w-full" size="sm">
                        {{ __('Checkout') }}
                    </flux:button>
                </div>
            @else
                <div class="px-4 py-6 text-center">
                    <flux:icon icon="shopping-cart" class="mx-auto mb-2 size-8 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Your cart is empty') }}</flux:text>
                </div>

                <flux:menu.separator />

                <div class="p-4">
                    <flux:button variant="outline" :href="route('products.index')" wire:navigate class="w-full" size="sm">
                        {{ __('Continue Shopping') }}
                    </flux:button>
                </div>
            @endif
        </flux:menu>
    </flux:dropdown>
</div>
