<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Title('User Management - Marines of Tahlequah')]
#[Layout('layouts.app')]
class extends Component {
    use WithPagination;

    public ?int $selectedUserId = null;
    public string $selectedUserRole = '';
    public string $selectedUserName = '';
    public string $selectedUserEmail = '';

    public ?int $deletingUserId = null;
    public string $deletingUserName = '';
    public string $deletingUserEmail = '';

    public ?string $statusMessage = null;
    public string $statusVariant = 'success';

    public function mount(): void
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }
    }

    public function with(): array
    {
        return [
            'users' => User::orderBy('name')->paginate(15),
        ];
    }

    public function updateUserRole(int $userId, string $newRole): void
    {
        if (!in_array($newRole, ['user', 'admin', 'superadmin'], true)) {
            return;
        }

        if ($userId === Auth::id()) {
            $this->statusMessage = __('You cannot change your own role.');
            $this->statusVariant = 'danger';
            Flux::toast(
                text: __('You cannot change your own role.'),
                heading: __('Error'),
                variant: 'danger',
            );
            return;
        }

        $user = User::findOrFail($userId);

        if ($user->role === 'superadmin' && $newRole !== 'superadmin') {
            $superAdminCount = User::where('role', 'superadmin')->count();
            if ($superAdminCount <= 1) {
                $this->statusMessage = __('Cannot remove the last super admin.');
                $this->statusVariant = 'danger';
                Flux::toast(
                    text: __('Cannot remove the last super admin.'),
                    heading: __('Error'),
                    variant: 'danger',
                );
                return;
            }
        }

        $user->update(['role' => $newRole]);

        $this->statusMessage = __("Role for {$user->name} changed to " . ucfirst($newRole) . ".");
        $this->statusVariant = 'success';

        Flux::toast(
            text: __("Role for {$user->name} changed to " . ucfirst($newRole) . "."),
            heading: __('Success'),
            variant: 'success',
        );
    }

    public function openRoleModal(int $userId, ?string $name = null, ?string $currentRole = null): void
    {
        $user = User::findOrFail($userId);
        $this->selectedUserId = $user->id;
        $this->selectedUserName = $name ?? $user->name;
        $this->selectedUserEmail = $user->email;
        $this->selectedUserRole = $currentRole ?? $user->role;
        $this->statusMessage = null;
    }

    public function closeRoleModal(): void
    {
        $this->selectedUserId = null;
        $this->selectedUserName = '';
        $this->selectedUserEmail = '';
        $this->selectedUserRole = '';
    }

    public function updateRole(): void
    {
        $this->validate([
            'selectedUserRole' => 'required|in:user,admin,superadmin',
        ]);

        if ($this->selectedUserId === Auth::id()) {
            $this->statusMessage = __('You cannot change your own role.');
            $this->statusVariant = 'danger';
            Flux::toast(
                text: __('You cannot change your own role.'),
                heading: __('Error'),
                variant: 'danger',
            );
            $this->closeRoleModal();
            return;
        }

        $user = User::findOrFail($this->selectedUserId);

        if ($user->role === 'superadmin' && $this->selectedUserRole !== 'superadmin') {
            $superAdminCount = User::where('role', 'superadmin')->count();
            if ($superAdminCount <= 1) {
                $this->statusMessage = __('Cannot remove the last super admin.');
                $this->statusVariant = 'danger';
                Flux::toast(
                    text: __('Cannot remove the last super admin.'),
                    heading: __('Error'),
                    variant: 'danger',
                );
                $this->closeRoleModal();
                return;
            }
        }

        $user->update(['role' => $this->selectedUserRole]);

        $userName = $user->name;
        $roleName = ucfirst($this->selectedUserRole);

        $this->closeRoleModal();

        $this->statusMessage = __("Role for {$userName} updated to {$roleName}.");
        $this->statusVariant = 'success';

        Flux::toast(
            text: __("Role for {$userName} updated to {$roleName}."),
            heading: __('Success'),
            variant: 'success',
        );
    }

    public function openDeleteModal(int $userId, ?string $name = null): void
    {
        $user = User::findOrFail($userId);
        $this->deletingUserId = $user->id;
        $this->deletingUserName = $name ?? $user->name;
        $this->deletingUserEmail = $user->email;
        $this->statusMessage = null;
    }

    public function closeDeleteModal(): void
    {
        $this->deletingUserId = null;
        $this->deletingUserName = '';
        $this->deletingUserEmail = '';
    }

    public function deleteUser(): void
    {
        if ($this->deletingUserId === Auth::id()) {
            $this->statusMessage = __('You cannot delete your own account here.');
            $this->statusVariant = 'danger';
            Flux::toast(
                text: __('You cannot delete your own account here.'),
                heading: __('Error'),
                variant: 'danger',
            );
            $this->closeDeleteModal();
            return;
        }

        $user = User::findOrFail($this->deletingUserId);

        if ($user->role === 'superadmin' && User::where('role', 'superadmin')->count() <= 1) {
            $this->statusMessage = __('Cannot delete the last super admin.');
            $this->statusVariant = 'danger';
            Flux::toast(
                text: __('Cannot delete the last super admin.'),
                heading: __('Error'),
                variant: 'danger',
            );
            $this->closeDeleteModal();
            return;
        }

        $deletedName = $user->name;
        $user->delete();
        $this->closeDeleteModal();

        $this->statusMessage = __("User \"{$deletedName}\" has been deleted.");
        $this->statusVariant = 'success';

        Flux::toast(
            text: __("User \"{$deletedName}\" has been deleted."),
            heading: __('Deleted'),
            variant: 'success',
        );
    }
}; ?>

<div class="py-8">
    <div class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('User Role Management') }}</flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">
                {{ __('Manage accounts, permissions, and administrative access for Marines of Tahlequah') }}
            </flux:subheading>
        </div>
    </div>

    {{-- Status Banner Notification --}}
    @if ($statusMessage)
        <div class="mb-6 flex items-center justify-between p-4 rounded-xl border {{ $statusVariant === 'danger' ? 'bg-red-500/10 border-red-500/30 text-red-500' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' }}">
            <div class="flex items-center gap-3">
                @if ($statusVariant === 'danger')
                    <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                @else
                    <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @endif
                <span class="text-sm font-semibold">{{ $statusMessage }}</span>
            </div>
            <button type="button" wire:click="$set('statusMessage', null)" class="text-current opacity-70 hover:opacity-100 p-1 cursor-pointer">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    {{-- Users Table --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/80 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    <tr>
                        <th class="py-4 px-6">{{ __('User') }}</th>
                        <th class="py-4 px-6">{{ __('Email') }}</th>
                        <th class="py-4 px-6">{{ __('Role') }}</th>
                        <th class="py-4 px-6">{{ __('Registered') }}</th>
                        <th class="py-4 px-6 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800/80 text-zinc-700 dark:text-zinc-300">
                    @foreach ($users as $user)
                        <tr wire:key="user-row-{{ $user->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition">
                            <td class="py-4 px-6 font-medium text-zinc-900 dark:text-white flex items-center gap-3">
                                <flux:avatar :name="$user->name" :initials="$user->initials()" size="sm" />
                                <span>{{ $user->name }}</span>
                                @if ($user->id === auth()->id())
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-amber-500 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                                        {{ __('You') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-zinc-500 dark:text-zinc-400">{{ $user->email }}</td>
                            <td class="py-4 px-6">
                                @if ($user->id === auth()->id())
                                    <flux:badge color="red" size="sm" inset="top bottom">
                                        {{ __('Super Admin') }}
                                    </flux:badge>
                                @else
                                    <div class="inline-flex items-center gap-2">
                                        <select
                                            wire:key="select-role-{{ $user->id }}"
                                            wire:change="updateUserRole({{ $user->id }}, $event.target.value)"
                                            class="text-xs font-semibold rounded-lg px-2.5 py-1.5 border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 focus:ring-2 focus:ring-amber-500 focus:outline-hidden cursor-pointer"
                                        >
                                            <option value="user" @selected($user->role === 'user')>{{ __('User') }}</option>
                                            <option value="admin" @selected($user->role === 'admin')>{{ __('Admin') }}</option>
                                            <option value="superadmin" @selected($user->role === 'superadmin')>{{ __('Super Admin') }}</option>
                                        </select>
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-zinc-500 dark:text-zinc-400">
                                {{ $user->created_at->format('M j, Y') }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($user->id !== auth()->id())
                                        <button
                                            type="button"
                                            wire:key="btn-role-{{ $user->id }}"
                                            wire:click="openRoleModal({{ $user->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-zinc-700 dark:text-zinc-200 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition cursor-pointer"
                                        >
                                            <svg class="size-3.5 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>{{ __('Change Role') }}</span>
                                        </button>

                                        <button
                                            type="button"
                                            wire:key="btn-delete-{{ $user->id }}"
                                            wire:click="openDeleteModal({{ $user->id }})"
                                            title="Delete User"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-950/70 border border-red-200 dark:border-red-900/50 transition cursor-pointer"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>{{ __('Delete') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Edit Role Modal --}}
    @if ($selectedUserId)
        <div wire:key="role-modal-container-{{ $selectedUserId }}" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity" wire:click="closeRoleModal"></div>

                {{-- Dialog Panel --}}
                <div class="relative w-full max-w-md transform rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 text-left shadow-2xl transition-all z-10 text-zinc-900 dark:text-zinc-100">
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-200 dark:border-zinc-800 mb-5">
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('Change User Role') }}</h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                {{ __('Updating permissions for') }} <span class="font-bold text-amber-500">{{ $selectedUserName }}</span>
                            </p>
                        </div>
                        <button
                            type="button"
                            wire:click="closeRoleModal"
                            class="rounded-lg p-1.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit="updateRole" class="space-y-5">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">
                                {{ __('Select New Role') }}
                            </label>
                            <select
                                wire:model="selectedUserRole"
                                class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-white font-medium focus:ring-2 focus:ring-amber-500 focus:outline-hidden"
                            >
                                <option value="user">{{ __('User (Standard Community Member)') }}</option>
                                <option value="admin">{{ __('Admin (Manage Events & Content)') }}</option>
                                <option value="superadmin">{{ __('Super Admin (Full Administrative Access)') }}</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                            <button
                                type="button"
                                wire:click="closeRoleModal"
                                class="px-4 py-2 text-sm font-medium rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2 text-sm font-semibold rounded-xl bg-red-700 hover:bg-red-600 text-white shadow-md transition cursor-pointer"
                            >
                                {{ __('Save Role') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete User Modal --}}
    @if ($deletingUserId)
        <div wire:key="delete-modal-container-{{ $deletingUserId }}" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity" wire:click="closeDeleteModal"></div>

                {{-- Dialog Panel --}}
                <div class="relative w-full max-w-md transform rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 text-left shadow-2xl transition-all z-10 text-zinc-900 dark:text-zinc-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex size-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/70 text-red-600 dark:text-red-400 shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('Delete User Account') }}</h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</p>
                        </div>
                    </div>

                    <p class="text-sm text-zinc-600 dark:text-zinc-300 mb-6">
                        {{ __('Are you sure you want to permanently delete the account for') }} <span class="font-bold text-zinc-900 dark:text-white">{{ $deletingUserName }}</span>?
                    </p>

                    <div class="flex justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                        <button
                            type="button"
                            wire:click="closeDeleteModal"
                            class="px-4 py-2 text-sm font-medium rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="button"
                            wire:click="deleteUser"
                            class="px-5 py-2 text-sm font-semibold rounded-xl bg-red-700 hover:bg-red-600 text-white shadow-md transition cursor-pointer"
                        >
                            {{ __('Delete User') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
