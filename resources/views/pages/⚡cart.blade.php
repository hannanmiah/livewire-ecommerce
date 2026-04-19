<?php

use App\Services\CartService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Shopping Cart')]
class extends Component {
    public string $couponCode = '';

    private CartService $cartService;

    public function boot(CartService $cartService): void
    {
        $this->cartService = $cartService;
    }

    #[Computed]
    public function cart()
    {
        return $this->cartService->getCartForContext();
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        if (! $this->cart) {
            return;
        }

        $this->cartService->updateItemQuantity($this->cart, $itemId, $quantity);
        unset($this->cart);
    }

    public function removeItem(int $itemId): void
    {
        if (! $this->cart) {
            return;
        }

        $this->cartService->removeItem($this->cart, $itemId);
        Flux::toast(variant: 'success', text: __('Item removed from cart.'));
        unset($this->cart);
    }

    public function applyCoupon(): void
    {
        if (! $this->cart) {
            return;
        }

        if ($this->cartService->applyCoupon($this->cart, $this->couponCode)) {
            Flux::toast(variant: 'success', text: __('Coupon applied.'));
            $this->reset('couponCode');
        } else {
            Flux::toast(variant: 'danger', text: __('Invalid or expired coupon.'));
        }

        unset($this->cart);
    }

    public function removeCoupon(): void
    {
        if (! $this->cart) {
            return;
        }

        $this->cartService->removeCoupon($this->cart);
        Flux::toast(variant: 'success', text: __('Coupon removed.'));
        unset($this->cart);
    }

    public function clearCart(): void
    {
        if (! $this->cart) {
            return;
        }

        $this->cartService->clearCart($this->cart);
        Flux::toast(variant: 'success', text: __('Cart cleared.'));
        unset($this->cart);
    }
}; ?>
<div>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('Home') }}</a>
            <flux:icon icon="chevron-right" class="size-3.5" />
            <span class="text-zinc-700 dark:text-zinc-300">{{ __('Shopping Cart') }}</span>
        </nav>

        <flux:heading level="lg">{{ __('Shopping Cart') }}</flux:heading>

        @php $cart = $this->cart; @endphp

        @if($cart && $cart->items->count() > 0)
            <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-3">
                {{-- Left: Cart Items --}}
                <div class="space-y-4 lg:col-span-2">
                    @foreach($cart->items as $item)
                        @php
                            $product = $item->variant->product;
                            $thumbnail = $product->getFirstMediaUrl('thumbnail');
                        @endphp
                        <div wire:key="cart-item-{{ $item->id }}" class="flex gap-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            {{-- Product Thumbnail --}}
                            <div class="size-24 flex-shrink-0 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                @if($thumbnail)
                                    <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="size-full object-cover" />
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <flux:icon icon="photo" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                    </div>
                                @endif
                            </div>

                            {{-- Product Details --}}
                            <div class="flex flex-1 flex-col justify-between">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="text-sm font-semibold text-zinc-900 hover:text-zinc-600 dark:text-zinc-100 dark:hover:text-zinc-300">
                                            {{ $product->name }}
                                        </a>
                                        @if($item->description)
                                            <flux:text class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $item->description }}</flux:text>
                                        @endif
                                    </div>

                                    {{-- Remove Button --}}
                                    <flux:button
                                        icon="trash"
                                        variant="subtle"
                                        size="sm"
                                        wire:click="removeItem({{ $item->id }})"
                                        class="flex-shrink-0 text-zinc-400 hover:text-red-500 dark:text-zinc-500 dark:hover:text-red-400"
                                    />
                                </div>

                                <div class="mt-2 flex items-center justify-between">
                                    {{-- Unit Price --}}
                                    <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                                        ${{ number_format((float) $item->price, 2) }}
                                    </flux:text>

                                    {{-- Quantity Controls --}}
                                    <div class="flex items-center gap-1">
                                        <flux:button
                                            icon="minus"
                                            variant="outline"
                                            size="sm"
                                            square
                                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                        />
                                        <flux:input
                                            type="number"
                                            wire:model.live.debounce.500ms=""
                                            value="{{ $item->quantity }}"
                                            wire:change="updateQuantity({{ $item->id }}, $event.target.value)"
                                            min="1"
                                            class="w-16 text-center"
                                            size="sm"
                                        />
                                        <flux:button
                                            icon="plus"
                                            variant="outline"
                                            size="sm"
                                            square
                                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                        />
                                    </div>

                                    {{-- Line Total --}}
                                    <flux:text variant="strong" class="text-sm">
                                        ${{ number_format((float) $item->total, 2) }}
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Clear Cart --}}
                    <div class="flex justify-end">
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="trash"
                            wire:click="clearCart"
                            class="text-zinc-500 hover:text-red-500 dark:text-zinc-400 dark:hover:text-red-400"
                        >
                            {{ __('Clear Cart') }}
                        </flux:button>
                    </div>
                </div>

                {{-- Right: Cart Summary --}}
                <div>
                    <flux:card class="space-y-4">
                        <flux:heading size="lg">{{ __('Order Summary') }}</flux:heading>

                        {{-- Coupon Section --}}
                        @if($cart->coupon_code)
                            <div class="flex items-center justify-between rounded-md bg-green-50 px-3 py-2 dark:bg-green-900/20">
                                <div class="flex items-center gap-2">
                                    <flux:icon icon="tag" class="size-4 text-green-600 dark:text-green-400" />
                                    <flux:text class="text-sm font-medium text-green-700 dark:text-green-400">
                                        {{ strtoupper($cart->coupon_code) }}
                                    </flux:text>
                                </div>
                                <flux:button
                                    icon="x-mark"
                                    variant="subtle"
                                    size="sm"
                                    wire:click="removeCoupon"
                                    class="text-green-600 hover:text-red-500 dark:text-green-400 dark:hover:text-red-400"
                                />
                            </div>
                        @else
                            <form wire:submit="applyCoupon" class="flex gap-2">
                                <flux:input
                                    wire:model="couponCode"
                                    :placeholder="__('Coupon code')"
                                    class="flex-1"
                                    size="sm"
                                />
                                <flux:button type="submit" variant="outline" size="sm">
                                    {{ __('Apply') }}
                                </flux:button>
                            </form>
                        @endif

                        <flux:separator />

                        {{-- Totals --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Subtotal') }}</flux:text>
                                <flux:text class="text-sm">${{ number_format((float) $cart->subtotal, 2) }}</flux:text>
                            </div>

                            @if($cart->discount > 0)
                                <div class="flex items-center justify-between">
                                    <flux:text class="text-sm text-green-600 dark:text-green-400">{{ __('Discount') }}</flux:text>
                                    <flux:text class="text-sm text-green-600 dark:text-green-400">-${{ number_format((float) $cart->discount, 2) }}</flux:text>
                                </div>
                            @endif

                            <flux:separator />

                            <div class="flex items-center justify-between">
                                <flux:text variant="strong">{{ __('Total') }}</flux:text>
                                <flux:text variant="strong" class="text-lg">${{ number_format((float) $cart->total, 2) }}</flux:text>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="space-y-2">
                            <flux:button
                                variant="primary"
                                :href="route('checkout')"
                                wire:navigate
                                class="w-full"
                            >
                                {{ __('Proceed to Checkout') }}
                            </flux:button>

                            <flux:button
                                variant="ghost"
                                :href="route('products.index')"
                                wire:navigate
                                class="w-full"
                            >
                                {{ __('Continue Shopping') }}
                            </flux:button>
                        </div>
                    </flux:card>
                </div>
            </div>
        @else
            {{-- Empty Cart State --}}
            <flux:card class="mt-6 py-12 text-center">
                <flux:icon icon="shopping-cart" class="mx-auto mb-4 size-16 text-zinc-300 dark:text-zinc-600" />
                <flux:heading>{{ __('Your cart is empty') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Browse our products and add items to your cart.') }}</flux:text>
                <flux:button
                    variant="primary"
                    :href="route('products.index')"
                    wire:navigate
                    class="mt-4"
                >
                    {{ __('Continue Shopping') }}
                </flux:button>
            </flux:card>
        @endif
    </div>
</div>
