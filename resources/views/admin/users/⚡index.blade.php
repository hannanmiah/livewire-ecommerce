<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin')]
#[Title('Users')]
class extends Component {
    use WithPagination;

    public string $search = '';

    public ?string $filter_role = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::withCount('orders')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%")))
            ->when($this->filter_role, fn ($q) => $q->where('role', $this->filter_role))
            ->latest()
            ->paginate(15);
    }
}; ?>
<flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading level="lg">{{ __('Users') }}</flux:heading>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row mb-6">
            <flux:input wire:model.live.debounce="search" :placeholder="__('Search by name or email...')" icon="magnifying-glass" class="sm:max-w-sm" />

            <flux:select wire:model.live="filter_role" :placeholder="__('All Roles')" class="sm:max-w-xs">
                <flux:select.option value="">{{ __('All Roles') }}</flux:select.option>
                <flux:select.option value="admin">{{ __('Admin') }}</flux:select.option>
                <flux:select.option value="editor">{{ __('Editor') }}</flux:select.option>
                <flux:select.option value="customer">{{ __('Customer') }}</flux:select.option>
            </flux:select>
        </div>

        <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Email') }}</flux:table.column>
                <flux:table.column>{{ __('Phone') }}</flux:table.column>
                <flux:table.column>{{ __('Role') }}</flux:table.column>
                <flux:table.column>{{ __('Orders') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell variant="strong">{{ $user->name }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>{{ $user->phone ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            @php
                                $roleColors = [
                                    'admin' => 'red',
                                    'editor' => 'blue',
                                    'customer' => 'zinc',
                                ];
                            @endphp
                            <flux:badge color="{{ $roleColors[$user->role] ?? 'zinc' }}" size="sm">
                                {{ ucfirst($user->role) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $user->orders_count }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button :href="route('admin.users.show', $user)" variant="ghost" size="sm" wire:navigate>
                                {{ __('View') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:main>
