<?php

use App\Models\Address;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Addresses')]
class extends Component {
    public ?int $editing_address_id = null;

    public string $type = 'shipping';

    public string $address_line = '';

    public string $city = '';

    public string $postal_code = '';

    public string $country = '';

    public bool $is_default = false;

    public bool $show_form = false;

    #[Computed]
    public function addresses()
    {
        return auth()->user()->addresses()->orderByDesc('is_default')->orderByDesc('created_at')->get();
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:billing,shipping',
            'address_line' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'nullable|string',
            'country' => 'required|string',
        ];
    }

    public function addAddress(): void
    {
        $this->reset(['editing_address_id', 'type', 'address_line', 'city', 'postal_code', 'country', 'is_default']);
        $this->show_form = true;
    }

    public function editAddress(int $addressId): void
    {
        $address = Address::findOrFail($addressId);
        $this->editing_address_id = $addressId;
        $this->type = $address->type;
        $this->address_line = $address->address_line;
        $this->city = $address->city;
        $this->postal_code = $address->postal_code ?? '';
        $this->country = $address->country;
        $this->is_default = $address->is_default;
        $this->show_form = true;
    }

    public function saveAddress(): void
    {
        $this->validate();

        if ($this->is_default) {
            auth()->user()->addresses()->where('type', $this->type)->update(['is_default' => false]);
        }

        if ($this->editing_address_id) {
            Address::findOrFail($this->editing_address_id)->update([
                'type' => $this->type,
                'address_line' => $this->address_line,
                'city' => $this->city,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
                'is_default' => $this->is_default,
            ]);
        } else {
            auth()->user()->addresses()->create([
                'type' => $this->type,
                'address_line' => $this->address_line,
                'city' => $this->city,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
                'is_default' => $this->is_default,
            ]);
        }

        $this->show_form = false;
        unset($this->addresses);
        Flux::toast(variant: 'success', text: __('Address saved.'));
    }

    public function deleteAddress(int $addressId): void
    {
        Address::findOrFail($addressId)->delete();
        unset($this->addresses);
        Flux::toast(variant: 'success', text: __('Address deleted.'));
    }

    public function setDefault(int $addressId): void
    {
        $address = Address::findOrFail($addressId);
        auth()->user()->addresses()->where('type', $address->type)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        unset($this->addresses);
        Flux::toast(variant: 'success', text: __('Default address updated.'));
    }
}; ?>
<div>
    <div class="container mx-auto px-4 py-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('Home') }}</a>
            <flux:icon icon="chevron-right" class="size-3.5" />
            <span class="text-zinc-700 dark:text-zinc-300">{{ __('My Addresses') }}</span>
        </nav>

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <flux:heading level="lg">{{ __('My Addresses') }}</flux:heading>

            <flux:button variant="primary" icon="plus" wire:click="addAddress">
                {{ __('Add Address') }}
            </flux:button>
        </div>

        {{-- Add/Edit Form --}}
        @if($show_form)
            <div class="mb-8 rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="md" class="mb-4">
                    {{ $editing_address_id ? __('Edit Address') : __('Add New Address') }}
                </flux:heading>

                <form wire:submit="saveAddress" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('Type') }}</flux:label>
                            <flux:select wire:model="type">
                                <flux:select.option value="shipping">{{ __('Shipping') }}</flux:select.option>
                                <flux:select.option value="billing">{{ __('Billing') }}</flux:select.option>
                            </flux:select>
                            <flux:error name="type" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Country') }}</flux:label>
                            <flux:input wire:model="country" :placeholder="__('Country')" />
                            <flux:error name="country" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>{{ __('Address Line') }}</flux:label>
                        <flux:input wire:model="address_line" :placeholder="__('Street address')" />
                        <flux:error name="address_line" />
                    </flux:field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>{{ __('City') }}</flux:label>
                            <flux:input wire:model="city" :placeholder="__('City')" />
                            <flux:error name="city" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Postal Code') }}</flux:label>
                            <flux:input wire:model="postal_code" :placeholder="__('Postal code')" />
                            <flux:error name="postal_code" />
                        </flux:field>
                    </div>

                    <flux:checkbox wire:model="is_default" :label="__('Set as default address')" />

                    <div class="flex items-center gap-3">
                        <flux:button type="submit" variant="primary">
                            {{ $editing_address_id ? __('Update Address') : __('Save Address') }}
                        </flux:button>

                        <flux:button type="button" variant="ghost" wire:click="$set('show_form', false)">
                            {{ __('Cancel') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Address List --}}
        @if($this->addresses->count() > 0)
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->addresses as $address)
                    <div wire:key="address-{{ $address->id }}" class="relative rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                        @if($address->is_default)
                            <flux:badge color="blue" size="sm" class="absolute top-3 right-3">{{ __('Default') }}</flux:badge>
                        @endif

                        <div class="space-y-2">
                            <flux:badge color="zinc" size="sm">{{ ucfirst($address->type) }}</flux:badge>

                            <flux:text variant="strong" class="block">{{ $address->address_line }}</flux:text>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $address->city }}@if($address->postal_code), {{ $address->postal_code }}@endif
                            </flux:text>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $address->country }}</flux:text>
                        </div>

                        <div class="mt-4 flex items-center gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                            <flux:button variant="ghost" size="sm" wire:click="editAddress({{ $address->id }})">
                                {{ __('Edit') }}
                            </flux:button>

                            @if(! $address->is_default)
                                <flux:button variant="ghost" size="sm" wire:click="setDefault({{ $address->id }})">
                                    {{ __('Set Default') }}
                                </flux:button>
                            @endif

                            <flux:button variant="ghost" size="sm" wire:click="deleteAddress({{ $address->id }})" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                {{ __('Delete') }}
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <flux:icon icon="map-pin" class="size-16 text-zinc-300 dark:text-zinc-600 mb-4" />
                <flux:heading level="lg" class="mb-2">{{ __('No addresses yet') }}</flux:heading>
                <flux:text class="mb-6">{{ __('Add an address to speed up your checkout process.') }}</flux:text>
                <flux:button variant="primary" wire:click="addAddress">
                    {{ __('Add Address') }}
                </flux:button>
            </div>
        @endif
    </div>
</div>
