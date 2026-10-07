<x-layouts::auth :title="__('Reset password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset your password')" :description="__('Enter your email and new password below')" />

        <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email', $request->email)"
                type="email"
                required
                autofocus
                autocomplete="email"
            />

            <flux:input
                name="password"
                :label="__('New password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                viewable
            />

            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                viewable
            />

            <flux:button type="submit" variant="primary" class="w-full bg-red-700 hover:bg-red-600 text-white">
                {{ __('Reset Password') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
