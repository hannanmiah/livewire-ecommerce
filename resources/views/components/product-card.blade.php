<?php

use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Attributes\Computed;
use Livewire\Component;

return new class extends Component {
    public Product $product;

    public function mount()
    {
        // load variants and brand to avoid N+1 queries
        $this->product->load(['variants', 'brand']);
    }

    #[Computed]
    public function minPrice(): float
    {
        return $this->product->variants->min('price');
    }

    #[Computed]
    public function maxPrice(): float
    {
        return $this->product->variants->max('price');
    }

    #[Computed]
    public function minSalePrice(): ?float
    {
        return $this->product->variants->whereNotNull('sale_price')->min('sale_price');
    }

    #[Computed]
    public function hasSale(): bool
    {
        return $this->product->variants->contains(fn($v) => $v->is_on_sale);
    }

    #[Computed]
    public function lowerVariant(): ?ProductVariant
    {
        return $this->product->variants->sortBy('price')->first();
    }

    public function addToCart()
    {
        $this->js("console.log('Adding to cart...')");
        $variant = $this->lowerVariant;

        if ($variant) {
            Flux::toast('Your changes have been saved.');
        }
    }

}
?>


<a href="{{ route('products.show', $product->slug) }}" wire:navigate
   class="group block rounded-xl border border-zinc-200 bg-white overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1 dark:border-zinc-700 dark:bg-zinc-800">
    <div class="aspect-square overflow-hidden bg-zinc-100 dark:bg-zinc-700">
        @if($product->thumbnail)
            <img src="{{ $product->thumbnail }}" alt="{{ $product->name }}"
                 class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                 loading="lazy"/>
        @else
            <div class="flex h-full w-full items-center justify-center text-zinc-400 dark:text-zinc-500">
                <svg class="size-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                          d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                </svg>
            </div>
        @endif
    </div>

    <div class="p-4">
        <h3 class="mt-1 text-sm font-semibold text-zinc-900 dark:text-zinc-100 line-clamp-2">
            {{ $product->name }}
        </h3>

        <div class="flex justify-between">
            <div class="mt-2 flex items-center gap-2">
                <span class="text-sm font-bold text-red-600 dark:text-red-400">{{ money($this->lowerVariant?->sale_price) }}</span>
                @if($this->lowerVariant->sale_price && $this->lowerVariant->sale_price < $this->lowerVariant->price)
                    <span class="text-xs text-zinc-400 line-through">{{ money($this->lowerVariant->price) }}</span>
                @endif
            </div>

            <flux:button size="sm" icon="shopping-cart" variant="filled" />
        </div>
    </div>
</a>
