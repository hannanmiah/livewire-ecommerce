<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')]
class extends Component {
    #[Computed]
    public function productsCount()
    {
        return Product::count();
    }

    #[Computed]
    public function ordersCount()
    {
        return Order::count();
    }

    #[Computed]
    public function customersCount()
    {
        return User::where('role', 'customer')->count();
    }

    #[Computed]
    public function revenueCount()
    {
        return Order::where('payment_status', 'paid')->sum('total');
    }
}
?>
<flux:main>
    <flux:heading level="lg">{{ __('Admin Dashboard') }}</flux:heading>
    <flux:text>{{ __('Welcome to the admin panel.') }}</flux:text>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <flux:card>
            <flux:text class="text-sm text-zinc-500">Products</flux:text>
            <flux:heading level="lg">{{ $this->productsCount }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-sm text-zinc-500">Orders</flux:text>
            <flux:heading level="lg">{{ $this->ordersCount }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-sm text-zinc-500">Customers</flux:text>
            <flux:heading level="lg">{{ $this->customersCount }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-sm text-zinc-500">Revenue</flux:text>
            <flux:heading level="lg">
                ${{ $this->revenueCount }}</flux:heading>
        </flux:card>
    </div>
</flux:main>

