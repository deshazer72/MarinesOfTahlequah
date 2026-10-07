<x-layouts::auth :title="__('Confirm password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Confirm your password')" :description="__('This is a secure area. Please confirm your password before continuing.')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                viewable
            />

            <flux:button type="submit" variant="primary" class="w-full bg-red-700 hover:bg-red-600 text-white">
                {{ __('Confirm') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
