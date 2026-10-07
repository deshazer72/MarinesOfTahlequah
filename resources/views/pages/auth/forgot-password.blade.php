<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Enter your email and we will send you a password reset link')" />

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                placeholder="name@example.com"
            />

            <flux:button type="submit" variant="primary" class="w-full bg-red-700 hover:bg-red-600 text-white">
                {{ __('Email Password Reset Link') }}
            </flux:button>
        </form>

        <div class="text-sm text-center text-zinc-400">
            <span>{{ __('Remembered your password?') }}</span>
            <flux:link :href="route('login')" class="text-amber-400 hover:text-amber-300 font-medium" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
