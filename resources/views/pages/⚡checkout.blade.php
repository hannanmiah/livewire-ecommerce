<?php

use App\Models\Address;
use App\Services\CartService;
use App\Services\OrderService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Checkout')]
class extends Component {
    public int $step = 1;

    // Shipping address
    public ?int $selected_shipping_address = null;
    public string $shipping_address_line = '';
    public string $shipping_city = '';
    public string $shipping_postal_code = '';
    public string $shipping_country = '';

    // Billing address
    public bool $billing_same_as_shipping = true;
    public ?int $selected_billing_address = null;
    public string $billing_address_line = '';
    public string $billing_city = '';
    public string $billing_postal_code = '';
    public string $billing_country = '';

    private CartService $cartService;

    public function boot(CartService $cartService): void
    {
        $this->cartService = $cartService;
    }

    public function mount(): void
    {
        $cart = $this->cartService->getCartForContext();
        if (!$cart || $cart->items->isEmpty()) {
            $this->redirect(route('cart'), navigate: true);

            return;
        }

        $defaultAddress = Auth::user()->addresses()->where('type', 'shipping')->where('is_default', true)->first();
        if ($defaultAddress) {
            $this->selected_shipping_address = $defaultAddress->id;
        }
    }

    #[Computed]
    public function cart()
    {
        return $this->cartService->getCartForContext();
    }

    #[Computed]
    public function shippingAddresses()
    {
        return Auth::user()->addresses()->where('type', 'shipping')->get();
    }

    #[Computed]
    public function billingAddresses()
    {
        return Auth::user()->addresses()->where('type', 'billing')->get();
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->step++;
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function validateCurrentStep(): void
    {
        if ($this->step === 1) {
            if ($this->selected_shipping_address) {
                return;
            }

            $this->validate([
                'shipping_address_line' => 'required|string',
                'shipping_city' => 'required|string',
                'shipping_postal_code' => 'nullable|string',
                'shipping_country' => 'required|string',
            ]);
        }

        if ($this->step === 2 && !$this->billing_same_as_shipping) {
            if ($this->selected_billing_address) {
                return;
            }

            $this->validate([
                'billing_address_line' => 'required|string',
                'billing_city' => 'required|string',
                'billing_postal_code' => 'nullable|string',
                'billing_country' => 'required|string',
            ]);
        }

        if ($this->step === 3) {
            if (!$this->selected_shipping_address && (!$this->shipping_address_line || !$this->shipping_city || !$this->shipping_country)) {
                $this->step = 1;
                $this->validateCurrentStep();
                return;
            }

            if (!$this->billing_same_as_shipping && !$this->selected_billing_address && (!$this->billing_address_line || !$this->billing_city || !$this->billing_country)) {
                $this->step = 2;
                $this->validateCurrentStep();
                return;
            }
        }
    }

    public function placeOrder(OrderService $orderService): void
    {
        $this->validateCurrentStep();

        $cart = $this->cart;
        if (!$cart || $cart->items->isEmpty()) {
            Flux::toast(variant: 'danger', text: __('Your cart is empty.'));
            $this->redirect(route('cart'), navigate: true);

            return;
        }

        try {
            if ($this->selected_shipping_address) {
                $address = Address::find($this->selected_shipping_address);
                $shippingAddress = [
                    'address_line' => $address->address_line,
                    'city' => $address->city,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                ];
            } else {
                $shippingAddress = [
                    'address_line' => $this->shipping_address_line,
                    'city' => $this->shipping_city,
                    'postal_code' => $this->shipping_postal_code,
                    'country' => $this->shipping_country,
                ];
            }

            $billingAddress = null;
            if (!$this->billing_same_as_shipping) {
                if ($this->selected_billing_address) {
                    $addr = Address::find($this->selected_billing_address);
                    $billingAddress = [
                        'address_line' => $addr->address_line,
                        'city' => $addr->city,
                        'postal_code' => $addr->postal_code,
                        'country' => $addr->country,
                    ];
                } else {
                    $billingAddress = [
                        'address_line' => $this->billing_address_line,
                        'city' => $this->billing_city,
                        'postal_code' => $this->billing_postal_code,
                        'country' => $this->billing_country,
                    ];
                }
            }

            $order = $orderService->createOrderFromCart($cart, Auth::user(), $shippingAddress, $billingAddress);
            $this->redirect(route('checkout.payment', $order), navigate: true);
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }
}; ?>
<flux:main>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('home') }}" wire:navigate
               class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('Home') }}</a>
            <flux:icon icon="chevron-right" class="size-3.5"/>
            <a href="{{ route('cart') }}" wire:navigate
               class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('Cart') }}</a>
            <flux:icon icon="chevron-right" class="size-3.5"/>
            <span class="text-zinc-700 dark:text-zinc-300">{{ __('Checkout') }}</span>
        </nav>

        <flux:heading level="lg" class="mb-8">{{ __('Checkout') }}</flux:heading>

        {{-- Step Indicator --}}
        <div class="mb-8 flex items-center gap-2">
            @foreach([__('Shipping'), __('Billing'), __('Review')] as $num => $label)
                @if($num > 1)
                    <div class="h-px flex-1 @if($step >= $num) bg-zinc-900 dark:bg-zinc-100 @else bg-zinc-200 dark:bg-zinc-700 @endif"></div>
                @endif
                <div class="flex items-center gap-2">
                    <div
                            @class([
                                'flex size-8 items-center justify-center rounded-full text-sm font-medium',
                                'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' => $step >= $num,
                                'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' => $step < $num,
                            ])
                    >
                        @if($step > $num)
                            <flux:icon icon="check" class="size-4"/>
                        @else
                            {{ $num }}
                        @endif
                    </div>
                    <span
                        @class([
                            'text-sm font-medium hidden sm:inline',
                            'text-zinc-900 dark:text-zinc-100' => $step >= $num,
                            'text-zinc-400 dark:text-zinc-500' => $step < $num,
                        ])
                    >{{ $label }}</span>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            {{-- Main Form Area --}}
            <div class="lg:col-span-2">
                {{-- Step 1: Shipping Address --}}
                @if($step === 1)
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-6">{{ __('Shipping Address') }}</flux:heading>

                        {{-- Saved Addresses --}}
                        @if($this->shippingAddresses->isNotEmpty())
                            <flux:radio.group wire:model="selected_shipping_address">
                                @foreach($this->shippingAddresses as $address)
                                    <flux:radio
                                            wire:key="shipping-{{ $address->id }}"
                                            value="{{ $address->id }}"
                                            name="selected_shipping_address"
                                            label="{{$address->address_line}}"
                                            description="{{ $address->city }}, {{ $address->country }} {{ $address->postal_code }}"
                                    />
                                @endforeach
                            </flux:radio.group>

                            <flux:separator class="my-4"/>

                            <div class="mb-4">
                                <flux:text
                                        class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Or add a new address:') }}</flux:text>
                            </div>
                        @endif

                        {{-- New Address Form --}}
                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>{{ __('Address Line') }}</flux:label>
                                <flux:input wire:model="shipping_address_line"
                                            placeholder="{{ __('123 Main Street') }}"/>
                                <flux:error name="shipping_address_line"/>
                            </flux:field>

                            <div class="grid grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label>{{ __('City') }}</flux:label>
                                    <flux:input wire:model="shipping_city" placeholder="{{ __('City') }}"/>
                                    <flux:error name="shipping_city"/>
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('Postal Code') }}</flux:label>
                                    <flux:input wire:model="shipping_postal_code"
                                                placeholder="{{ __('Postal Code') }}"/>
                                    <flux:error name="shipping_postal_code"/>
                                </flux:field>
                            </div>

                            <flux:field>
                                <flux:label>{{ __('Country') }}</flux:label>
                                <flux:input wire:model="shipping_country" placeholder="{{ __('Country') }}"/>
                                <flux:error name="shipping_country"/>
                            </flux:field>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <flux:button variant="primary" wire:click="nextStep">
                                {{ __('Continue to Billing') }}
                            </flux:button>
                        </div>
                    </div>
                @endif

                {{-- Step 2: Billing Address --}}
                @if($step === 2)
                    <div wire:key="step-2"
                         class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="lg" class="mb-6">{{ __('Billing Address') }}</flux:heading>

                        <label class="mb-6 flex cursor-pointer items-center gap-3">
                            <flux:checkbox wire:model.live="billing_same_as_shipping"/>
                            <flux:text>{{ __('Same as shipping address') }}</flux:text>
                        </label>

                        @if(! $billing_same_as_shipping)
                            {{-- Saved Billing Addresses --}}
                            @if($this->billingAddresses->isNotEmpty())
                                <div class="mb-6 space-y-3">
                                    @foreach($this->billingAddresses as $address)
                                        <label
                                                wire:key="billing-{{ $address->id }}"
                                                @class([
                                                    'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors',
                                                    'border-zinc-900 bg-zinc-50 dark:border-zinc-100 dark:bg-zinc-700' => $selected_billing_address === $address->id,
                                                    'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600' => $selected_billing_address !== $address->id,
                                                ])
                                        >
                                            <flux:radio
                                                    wire:model="selected_billing_address"
                                                    value="{{ $address->id }}"
                                                    name="selected_billing_address"
                                            />
                                            <div class="flex-1">
                                                <flux:text variant="strong">{{ $address->address_line }}</flux:text>
                                                <flux:text class="text-sm">{{ $address->city }}
                                                    , {{ $address->country }} {{ $address->postal_code }}</flux:text>
                                                @if($address->is_default)
                                                    <flux:badge color="blue" size="sm"
                                                                class="mt-1">{{ __('Default') }}</flux:badge>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                <flux:separator class="my-4"/>

                                <div class="mb-4">
                                    <flux:text
                                            class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Or add a new address:') }}</flux:text>
                                </div>
                            @endif

                            {{-- New Billing Address Form --}}
                            <div class="space-y-4">
                                <flux:field>
                                    <flux:label>{{ __('Address Line') }}</flux:label>
                                    <flux:input wire:model="billing_address_line"
                                                placeholder="{{ __('123 Main Street') }}"/>
                                    <flux:error name="billing_address_line"/>
                                </flux:field>

                                <div class="grid grid-cols-2 gap-4">
                                    <flux:field>
                                        <flux:label>{{ __('City') }}</flux:label>
                                        <flux:input wire:model="billing_city" placeholder="{{ __('City') }}"/>
                                        <flux:error name="billing_city"/>
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>{{ __('Postal Code') }}</flux:label>
                                        <flux:input wire:model="billing_postal_code"
                                                    placeholder="{{ __('Postal Code') }}"/>
                                        <flux:error name="billing_postal_code"/>
                                    </flux:field>
                                </div>

                                <flux:field>
                                    <flux:label>{{ __('Country') }}</flux:label>
                                    <flux:input wire:model="billing_country" placeholder="{{ __('Country') }}"/>
                                    <flux:error name="billing_country"/>
                                </flux:field>
                            </div>
                        @endif

                        <div class="mt-6 flex items-center justify-between">
                            <flux:button variant="ghost" wire:click="previousStep">
                                {{ __('Back') }}
                            </flux:button>
                            <flux:button variant="primary" wire:click="nextStep">
                                {{ __('Continue to Review') }}
                            </flux:button>
                        </div>
                    </div>
                @endif

                {{-- Step 3: Order Review --}}
                @if($step === 3)
                    @php($cart = $this->cart)
                    <div wire:key="step-3" class="space-y-6">
                        {{-- Shipping Address Summary --}}
                        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center justify-between mb-4">
                                <flux:heading size="md">{{ __('Shipping Address') }}</flux:heading>
                                <flux:button variant="ghost" size="sm" wire:click="$set('step', 1)">
                                    {{ __('Edit') }}
                                </flux:button>
                            </div>
                            @if($selected_shipping_address)
                                @php($sa = $this->shippingAddresses->firstWhere('id', $selected_shipping_address))
                                <flux:text>{{ $sa->address_line }}</flux:text>
                                <flux:text class="text-sm">{{ $sa->city }}
                                    , {{ $sa->country }} {{ $sa->postal_code }}</flux:text>
                            @else
                                <flux:text>{{ $shipping_address_line }}</flux:text>
                                <flux:text class="text-sm">{{ $shipping_city }}
                                    , {{ $shipping_country }} {{ $shipping_postal_code }}</flux:text>
                            @endif
                        </div>

                        {{-- Billing Address Summary --}}
                        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center justify-between mb-4">
                                <flux:heading size="md">{{ __('Billing Address') }}</flux:heading>
                                <flux:button variant="ghost" size="sm" wire:click="$set('step', 2)">
                                    {{ __('Edit') }}
                                </flux:button>
                            </div>
                            @if($billing_same_as_shipping)
                                <flux:text
                                        class="text-sm text-zinc-500">{{ __('Same as shipping address') }}</flux:text>
                            @elseif($selected_billing_address)
                                @php($ba = $this->billingAddresses->firstWhere('id', $selected_billing_address))
                                <flux:text>{{ $ba->address_line }}</flux:text>
                                <flux:text class="text-sm">{{ $ba->city }}
                                    , {{ $ba->country }} {{ $ba->postal_code }}</flux:text>
                            @else
                                <flux:text>{{ $billing_address_line }}</flux:text>
                                <flux:text class="text-sm">{{ $billing_city }}
                                    , {{ $billing_country }} {{ $billing_postal_code }}</flux:text>
                            @endif
                        </div>

                        {{-- Cart Items --}}
                        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                            <flux:heading size="md" class="mb-4">{{ __('Order Items') }}</flux:heading>

                            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($cart->items as $item)
                                    <div wire:key="review-item-{{ $item->id }}"
                                         class="flex items-center gap-4 py-4 first:pt-0 last:pb-0">
                                        {{-- Product Image --}}
                                        <div class="size-16 flex-shrink-0 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                            @if($item->variant->product->getFirstMediaUrl('thumbnail'))
                                                <img src="{{ $item->variant->product->getFirstMediaUrl('thumbnail') }}"
                                                     alt="{{ $item->variant->product->name }}"
                                                     class="size-full object-cover"/>
                                            @else
                                                <div class="flex size-full items-center justify-center">
                                                    <flux:icon icon="photo"
                                                               class="size-6 text-zinc-300 dark:text-zinc-600"/>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Product Info --}}
                                        <div class="flex-1 min-w-0">
                                            <flux:text variant="strong"
                                                       class="line-clamp-1">{{ $item->variant->product->name }}</flux:text>
                                            @if($item->description)
                                                <flux:text
                                                        class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->description }}</flux:text>
                                            @endif
                                            <flux:text
                                                    class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Qty: :quantity', ['quantity' => $item->quantity]) }}</flux:text>
                                        </div>

                                        {{-- Price --}}
                                        <div class="text-right">
                                            <flux:text variant="strong">
                                                ${{ number_format((float) $item->total, 2) }}</flux:text>
                                            @if($item->quantity > 1)
                                                <flux:text class="block text-xs text-zinc-500 dark:text-zinc-400">
                                                    ${{ number_format((float) $item->price, 2) }} {{ __('each') }}</flux:text>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <flux:button variant="ghost" wire:click="previousStep">
                                {{ __('Back') }}
                            </flux:button>
                            <flux:button variant="primary" wire:click="placeOrder" wire:loading.attr="disabled">
                                <flux:icon icon="lock-closed" class="size-4"/>
                                {{ __('Place Order') }}
                            </flux:button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Order Summary Sidebar --}}
            @php($cart = $this->cart)
            @if($cart)
                <div class="lg:col-span-1" wire:key="checkout-summary">
                    <div class="sticky top-8 rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="md" class="mb-4">{{ __('Order Summary') }}</flux:heading>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Subtotal') }}</flux:text>
                                <flux:text>${{ number_format((float) $cart->subtotal, 2) }}</flux:text>
                            </div>

                            @if($cart->discount > 0)
                                <div class="flex justify-between text-green-600 dark:text-green-400">
                                    <flux:text>{{ __('Discount') }}</flux:text>
                                    <flux:text>-${{ number_format((float) $cart->discount, 2) }}</flux:text>
                                </div>
                            @endif

                            <div class="flex justify-between">
                                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Shipping') }}</flux:text>
                                <flux:text>{{ __('Free') }}</flux:text>
                            </div>

                            <flux:separator/>

                            <div class="flex justify-between text-base font-semibold">
                                <flux:text variant="strong">{{ __('Total') }}</flux:text>
                                <flux:text variant="strong">${{ number_format((float) $cart->total, 2) }}</flux:text>
                            </div>
                        </div>

                        @if($cart->coupon_code)
                            <div class="mt-4 flex items-center gap-2 rounded-md bg-green-50 px-3 py-2 dark:bg-green-900/20">
                                <flux:icon icon="tag" class="size-4 text-green-600 dark:text-green-400"/>
                                <flux:text
                                        class="text-xs text-green-700 dark:text-green-300">{{ __('Coupon: :code', ['code' => $cart->coupon_code]) }}</flux:text>
                            </div>
                        @endif

                        <flux:separator class="my-4"/>

                        <div class="space-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                            <div class="flex items-center gap-2">
                                <flux:icon icon="shield-check" class="size-4"/>
                                <span>{{ __('Secure checkout') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:icon icon="truck" class="size-4"/>
                                <span>{{ __('Free shipping on all orders') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</flux:main>
