<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Profile Settings')]
#[Layout('layouts.app')]
class extends Component {
    public string $name = '';
    public string $email = '';

    public string $delete_password = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(
            text: __('Profile updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );
    }

    public function deleteUser(): void
    {
        $this->validate([
            'delete_password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        if ($user->role === 'superadmin' && User::where('role', 'superadmin')->count() <= 1) {
            $this->addError('delete_password', __('Cannot delete the last super admin.'));
            return;
        }

        Auth::logout();

        $user->delete();

        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />
            <flux:input wire:model="email" :label="__('Email Address')" type="email" required autocomplete="email" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="bg-red-700 hover:bg-red-600 text-white">
                    {{ __('Save Changes') }}
                </flux:button>
            </div>
        </form>

        <flux:separator class="my-8" />

        <div class="space-y-4">
            <flux:heading size="base" class="text-red-500 font-semibold">{{ __('Delete Account') }}</flux:heading>
            <flux:text class="text-sm text-zinc-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently removed.') }}
            </flux:text>

            <flux:modal.trigger name="confirm-user-deletion">
                <flux:button variant="danger" size="sm">
                    {{ __('Delete Account') }}
                </flux:button>
            </flux:modal.trigger>

            <flux:modal name="confirm-user-deletion" class="max-w-md">
                <form wire:submit="deleteUser" class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Are you sure you want to delete your account?') }}</flux:heading>
                        <flux:subheading class="mt-2 text-zinc-400">
                            {{ __('Please enter your password to confirm you would like to permanently delete your account.') }}
                        </flux:subheading>
                    </div>

                    <flux:input
                        wire:model="delete_password"
                        :label="__('Password')"
                        type="password"
                        required
                        placeholder="••••••••"
                        viewable
                    />

                    <div class="flex justify-end gap-3">
                        <flux:modal.close>
                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                        </flux:modal.close>
                        <flux:button type="submit" variant="danger">{{ __('Delete Account') }}</flux:button>
                    </div>
                </form>
            </flux:modal>
        </div>
    </x-pages::settings.layout>
</section>
