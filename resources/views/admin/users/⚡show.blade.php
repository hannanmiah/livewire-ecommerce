<?php

use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')]
#[Title('User Details')]
class extends Component {
    public User $user;

    public string $role = '';

    public function mount(User $user): void
    {
        $this->user = $user->load(['orders' => fn ($q) => $q->latest()->limit(10)]);
        $this->role = $user->role;
    }

    public function updateRole(): void
    {
        $this->validate(['role' => 'required|in:admin,editor,customer']);
        $this->user->update(['role' => $this->role]);
        Flux::toast(variant: 'success', text: __('User role updated.'));
    }
} ?>
<flux:main>
    <div class="flex items-center justify-between mb-6">
        <flux:heading level="lg">{{ __('User Details') }}</flux:heading>

        <flux:button :href="route('admin.users.index')" variant="ghost" wire:navigate>
            {{ __('Back to Users') }}
        </flux:button>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Recent Orders --}}
            <flux:card>
                <flux:heading size="md" class="mb-4">{{ __('Recent Orders') }}</flux:heading>

                @if ($user->orders->isEmpty())
                    <flux:text class="text-zinc-500">{{ __('No orders found.') }}</flux:text>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Order #') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column>{{ __('Total') }}</flux:table.column>
                            <flux:table.column>{{ __('Date') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($user->orders as $order)
                                <flux:table.row :key="$order->id">
                                    <flux:table.cell variant="strong">
                                        <a href="{{ route('admin.orders.show', $order) }}" wire:navigate class="text-blue-600 hover:underline">
                                            {{ $order->order_number }}
                                        </a>
                                    </flux:table.cell>
                                    <flux:table.cell>
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
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $order->total }}</flux:table.cell>
                                    <flux:table.cell>{{ $order->created_at->format('M d, Y') }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </flux:card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- User Info --}}
            <flux:card>
                <flux:heading size="md" class="mb-4">{{ __('User Information') }}</flux:heading>

                <div class="space-y-3">
                    <div>
                        <flux:text class="text-zinc-500">{{ __('Name') }}</flux:text>
                        <flux:text>{{ $user->name }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-zinc-500">{{ __('Email') }}</flux:text>
                        <flux:text>{{ $user->email }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-zinc-500">{{ __('Phone') }}</flux:text>
                        <flux:text>{{ $user->phone ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-zinc-500">{{ __('Joined') }}</flux:text>
                        <flux:text>{{ $user->created_at->format('M d, Y') }}</flux:text>
                    </div>
                </div>
            </flux:card>

            {{-- Update Role --}}
            <flux:card>
                <flux:heading size="md" class="mb-4">{{ __('Update Role') }}</flux:heading>

                <form wire:submit="updateRole" class="space-y-4">
                    <flux:select wire:model="role" :label="__('Role')">
                        <flux:select.option value="admin">{{ __('Admin') }}</flux:select.option>
                        <flux:select.option value="editor">{{ __('Editor') }}</flux:select.option>
                        <flux:select.option value="customer">{{ __('Customer') }}</flux:select.option>
                    </flux:select>

                    <flux:error name="role" />

                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Update Role') }}</flux:button>
                </form>
            </flux:card>
        </div>
    </div>
</flux:main>
