<x-layouts::auth :title="__('Verify Email')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Verify email')" :description="__('Thanks for signing up! Please verify your email by clicking the link we sent you.')" />

        @if (session('status') == 'verification-link-sent')
            <div class="font-medium text-sm text-green-500 text-center">
                {{ __('A new verification link has been sent to your email address.') }}
            </div>
        @endif

        <div class="flex flex-col gap-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full bg-red-700 hover:bg-red-600 text-white">
                    {{ __('Resend Verification Email') }}
                </flux:button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="text-center">
                @csrf
                <flux:button variant="ghost" type="submit" class="text-sm text-zinc-400">
                    {{ __('Log out') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts::auth>
