<x-layouts::admin :title="__('Admin Dashboard')">
    <flux:main>
        <flux:heading level="lg">{{ __('Admin Dashboard') }}</flux:heading>
        <flux:text>{{ __('Welcome to the admin panel.') }}</flux:text>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <flux:card>
                <flux:text class="text-sm text-zinc-500">Products</flux:text>
                <flux:heading level="lg">{{ \App\Models\Product::count() }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-sm text-zinc-500">Orders</flux:text>
                <flux:heading level="lg">{{ \App\Models\Order::count() }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-sm text-zinc-500">Customers</flux:text>
                <flux:heading level="lg">{{ \App\Models\User::where('role', 'customer')->count() }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-sm text-zinc-500">Revenue</flux:text>
                <flux:heading level="lg">${{ \App\Models\Order::where('payment_status', 'paid')->sum('total') }}</flux:heading>
            </flux:card>
        </div>
    </flux:main>
</x-layouts::admin>
