<?php

use App\Models\Review;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Reviews')]
class extends Component {
    public ?int $editing_review_id = null;

    public int $edit_rating = 5;

    public string $edit_comment = '';

    public bool $show_edit_form = false;

    #[Computed]
    public function reviews()
    {
        return Review::where('user_id', auth()->id())
            ->with('product')
            ->latest()
            ->get();
    }

    public function editReview(int $reviewId): void
    {
        $review = Review::findOrFail($reviewId);
        $this->editing_review_id = $reviewId;
        $this->edit_rating = $review->rating;
        $this->edit_comment = $review->comment ?? '';
        $this->show_edit_form = true;
    }

    public function updateReview(): void
    {
        $this->validate([
            'edit_rating' => 'required|integer|min:1|max:5',
            'edit_comment' => 'nullable|string|max:1000',
        ]);

        Review::findOrFail($this->editing_review_id)->update([
            'rating' => $this->edit_rating,
            'comment' => $this->edit_comment,
        ]);

        $this->show_edit_form = false;
        unset($this->reviews);
        Flux::toast(variant: 'success', text: __('Review updated.'));
    }

    public function deleteReview(int $reviewId): void
    {
        Review::findOrFail($reviewId)->delete();
        unset($this->reviews);
        Flux::toast(variant: 'success', text: __('Review deleted.'));
    }
}; ?>
<div>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-700 dark:hover:text-zinc-300">{{ __('Home') }}</a>
            <flux:icon icon="chevron-right" class="size-3.5" />
            <span class="text-zinc-700 dark:text-zinc-300">{{ __('My Reviews') }}</span>
        </nav>

        {{-- Header --}}
        <div class="mb-8">
            <flux:heading level="lg">{{ __('My Reviews') }}</flux:heading>
        </div>

        {{-- Edit Form Modal --}}
        @if($show_edit_form)
            <div class="mb-8 rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="md" class="mb-4">{{ __('Edit Review') }}</flux:heading>

                <form wire:submit="updateReview" class="space-y-4">
                    <flux:field>
                        <flux:label>{{ __('Rating') }}</flux:label>
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    wire:click="$set('edit_rating', {{ $i }})"
                                    class="cursor-pointer p-0.5"
                                >
                                    @if($i <= $edit_rating)
                                        <svg class="size-7 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    @else
                                        <svg class="size-7 text-zinc-300 dark:text-zinc-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    @endif
                                </button>
                            @endfor
                        </div>
                        <flux:error name="edit_rating" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Comment') }}</flux:label>
                        <flux:textarea wire:model="edit_comment" :placeholder="__('Share your experience...')" rows="4" />
                        <flux:error name="edit_comment" />
                    </flux:field>

                    <div class="flex items-center gap-3">
                        <flux:button type="submit" variant="primary">{{ __('Update Review') }}</flux:button>
                        <flux:button type="button" variant="ghost" wire:click="$set('show_edit_form', false)">{{ __('Cancel') }}</flux:button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Reviews List --}}
        @if($this->reviews->count() > 0)
            <div class="space-y-4">
                @foreach($this->reviews as $review)
                    <div wire:key="review-{{ $review->id }}" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                {{-- Product Info --}}
                                @if($review->product)
                                    <a href="{{ route('products.show', $review->product->slug) }}" wire:navigate class="shrink-0">
                                        <div class="size-16 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                            @if($review->product->getFirstMediaUrl('thumbnail'))
                                                <img src="{{ $review->product->getFirstMediaUrl('thumbnail') }}" alt="{{ $review->product->name }}" class="size-full object-cover" />
                                            @else
                                                <div class="flex size-full items-center justify-center">
                                                    <flux:icon icon="photo" class="size-6 text-zinc-300 dark:text-zinc-600" />
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                @endif

                                <div class="space-y-1">
                                    @if($review->product)
                                        <a href="{{ route('products.show', $review->product->slug) }}" wire:navigate class="text-sm font-medium text-zinc-900 hover:text-blue-600 dark:text-zinc-100 dark:hover:text-blue-400">
                                            {{ $review->product->name }}
                                        </a>
                                    @endif

                                    {{-- Rating Stars --}}
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <svg class="size-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                            @else
                                                <svg class="size-4 text-zinc-300 dark:text-zinc-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                            @endif
                                        @endfor
                                        <flux:text class="ml-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $review->created_at->diffForHumans() }}</flux:text>
                                    </div>

                                    @if($review->comment)
                                        <flux:text class="text-sm">{{ $review->comment }}</flux:text>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-1">
                                <flux:button variant="ghost" size="sm" wire:click="editReview({{ $review->id }})">
                                    {{ __('Edit') }}
                                </flux:button>
                                <flux:button variant="ghost" size="sm" wire:click="deleteReview({{ $review->id }})" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <flux:icon icon="chat-bubble-left-right" class="size-16 text-zinc-300 dark:text-zinc-600 mb-4" />
                <flux:heading level="lg" class="mb-2">{{ __('No reviews yet') }}</flux:heading>
                <flux:text class="mb-6">{{ __('You haven\'t written any reviews yet. Purchase a product to leave a review.') }}</flux:text>
                <flux:button :href="route('products.index')" variant="primary" wire:navigate>
                    {{ __('Browse Products') }}
                </flux:button>
            </div>
        @endif
    </div>
</div>
