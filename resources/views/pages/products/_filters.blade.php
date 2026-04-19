{{-- Category Filter --}}
<div>
    <flux:heading size="sm" class="mb-3">{{ __('Categories') }}</flux:heading>
    <div class="space-y-2">
        @foreach($this->categories as $cat)
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <flux:checkbox
                    wire:model.live="selected_categories"
                    value="{{ $cat->id }}"
                />
                <span class="text-zinc-700 dark:text-zinc-300">{{ $cat->name }}</span>
                <span class="text-xs text-zinc-400">({{ $cat->products_count }})</span>
            </label>
        @endforeach
    </div>
</div>

{{-- Brand Filter --}}
<div>
    <flux:heading size="sm" class="mb-3">{{ __('Brands') }}</flux:heading>
    <div class="space-y-2">
        @foreach($this->brands as $b)
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <flux:checkbox
                    wire:model.live="selected_brands"
                    value="{{ $b->id }}"
                />
                <span class="text-zinc-700 dark:text-zinc-300">{{ $b->name }}</span>
                <span class="text-xs text-zinc-400">({{ $b->products_count }})</span>
            </label>
        @endforeach
    </div>
</div>

{{-- Attribute Filters (dynamic) --}}
@foreach($this->filterAttributes as $attribute)
    <div>
        <flux:heading size="sm" class="mb-3">{{ $attribute->name }}</flux:heading>
        <div class="space-y-2">
            @foreach($attribute->values as $attrValue)
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <flux:checkbox
                        wire:model.live="selected_attributes.{{ $attribute->name }}"
                        value="{{ $attrValue->value }}"
                    />
                    <span class="text-zinc-700 dark:text-zinc-300">{{ $attrValue->value }}</span>
                </label>
            @endforeach
        </div>
    </div>
@endforeach

{{-- Price Range Filter --}}
<div>
    <flux:heading size="sm" class="mb-3">{{ __('Price Range') }}</flux:heading>
    <div class="flex items-center gap-2">
        <flux:input
            wire:model.live.debounce.500ms="price_min"
            type="number"
            placeholder="{{ __('Min') }}"
            size="sm"
            class="w-full"
        />
        <span class="text-zinc-400">-</span>
        <flux:input
            wire:model.live.debounce.500ms="price_max"
            type="number"
            placeholder="{{ __('Max') }}"
            size="sm"
            class="w-full"
        />
    </div>
</div>

{{-- Clear Filters --}}
@if($this->hasActiveFilters())
    <flux:button variant="ghost" size="sm" wire:click="clearFilters" class="w-full">
        {{ __('Clear all filters') }}
    </flux:button>
@endif
