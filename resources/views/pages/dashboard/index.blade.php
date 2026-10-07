<?php

use App\Models\About;
use App\Models\Event;
use App\Models\SlideshowImage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Dashboard - Marines of Tahlequah')]
#[Layout('layouts.app')]
class extends Component {
    public function with(): array
    {
        $user = Auth::user();

        return [
            'user' => $user,
            'eventsCount' => Event::count(),
            'upcomingEvents' => Event::where('event_datetime', '>=', now())
                ->orderBy('event_datetime', 'asc')
                ->take(3)
                ->get(),
            'aboutCount' => About::count(),
            'slideshowCount' => SlideshowImage::where('is_active', true)->count(),
            'usersCount' => $user->isSuperAdmin() ? User::count() : 0,
        ];
    }
}; ?>

<div class="py-6 sm:py-8">
    {{-- Welcome Header --}}
    <div class="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" level="1">Welcome, {{ $user->name }}</flux:heading>
                @if ($user->role === 'superadmin')
                    <flux:badge color="red" size="sm">Super Admin</flux:badge>
                @elseif ($user->role === 'admin')
                    <flux:badge color="amber" size="sm">Admin</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm">Member</flux:badge>
                @endif
            </div>
            <flux:subheading class="mt-1 text-zinc-400">
                Marines of Tahlequah Member Portal &bull; Semper Fidelis
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($user->isAdmin() || $user->isSuperAdmin())
                <flux:button variant="primary" :href="route('events.create')" icon="plus" wire:navigate class="bg-red-700 hover:bg-red-600 text-white text-sm">
                    New Event
                </flux:button>
                <flux:button variant="ghost" :href="route('about.create')" icon="document-plus" wire:navigate class="text-sm text-zinc-300">
                    New About Entry
                </flux:button>
                <flux:button variant="ghost" :href="route('admin.slideshow')" icon="photo" wire:navigate class="text-sm text-zinc-300">
                    Manage Slideshow
                </flux:button>
            @endif
            @if ($user->isSuperAdmin())
                <flux:button variant="ghost" :href="route('users.index')" icon="users" wire:navigate class="text-sm text-zinc-300">
                    User Roles
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Stats Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Events Stat --}}
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 shadow-md dark:shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase font-bold tracking-wider text-zinc-500 dark:text-zinc-400">Total Events</span>
                <div class="flex size-9 items-center justify-center rounded-lg bg-red-100 dark:bg-red-950/60 border border-red-200 dark:border-red-800/40 text-red-700 dark:text-red-400">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-zinc-900 dark:text-white">{{ $eventsCount }}</p>
            <p class="mt-2 text-xs text-zinc-500">Upcoming & past community gatherings</p>
        </div>

        {{-- About Sections Stat --}}
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 shadow-md dark:shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase font-bold tracking-wider text-zinc-500 dark:text-zinc-400">About Articles</span>
                <div class="flex size-9 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/40 text-amber-700 dark:text-amber-400">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400">{{ $aboutCount }}</p>
            <p class="mt-2 text-xs text-zinc-500">Published organizational sections</p>
        </div>

        {{-- Slideshow Photos Stat --}}
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 shadow-md dark:shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase font-bold tracking-wider text-zinc-500 dark:text-zinc-400">Active Slides</span>
                <div class="flex size-9 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800/40 text-purple-700 dark:text-purple-400">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-purple-600 dark:text-purple-400">{{ $slideshowCount }}</p>
            <p class="mt-2 text-xs text-zinc-500">
                <a href="{{ route('admin.slideshow') }}" class="hover:underline text-purple-500" wire:navigate>Manage Carousel Photos &rarr;</a>
            </p>
        </div>

        {{-- Users Stat (if superadmin) or Donation Link --}}
        @if ($user->isSuperAdmin())
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 shadow-md dark:shadow-xl">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs uppercase font-bold tracking-wider text-zinc-500 dark:text-zinc-400">Registered Users</span>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/40 text-blue-700 dark:text-blue-400">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-blue-600 dark:text-blue-400">{{ $usersCount }}</p>
                <p class="mt-2 text-xs text-zinc-500">Active community accounts</p>
            </div>
        @else
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 shadow-md dark:shadow-xl">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs uppercase font-bold tracking-wider text-zinc-500 dark:text-zinc-400">Support Mission</span>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                    </div>
                </div>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">Donate</p>
                <a href="{{ route('donate') }}" class="mt-2 inline-block text-xs text-amber-600 dark:text-amber-400 hover:underline" wire:navigate>
                    View PayPal & Cash App QR Codes &rarr;
                </a>
            </div>
        @endif
    </div>

    {{-- Upcoming Events Preview --}}
    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-6 sm:p-8 shadow-md dark:shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="lg">Upcoming Events</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">Next community events and fundraisers</flux:subheading>
            </div>
            <flux:button variant="ghost" :href="route('events.index')" icon-trailing="arrow-right" wire:navigate class="text-sm text-amber-600 dark:text-amber-400">
                View All
            </flux:button>
        </div>

        @if ($upcomingEvents->isEmpty())
            <div class="text-center py-8 text-zinc-500 text-sm">
                No upcoming events currently scheduled. Check the full list or create one.
            </div>
        @else
            <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @foreach ($upcomingEvents as $evt)
                    <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="text-center px-3 py-1.5 rounded-lg bg-red-100 dark:bg-red-950/50 border border-red-200 dark:border-red-800/40">
                                <span class="block text-xs font-bold uppercase text-red-700 dark:text-red-400">{{ $evt->event_datetime->format('M') }}</span>
                                <span class="block text-lg font-black text-zinc-900 dark:text-white leading-none">{{ $evt->event_datetime->format('d') }}</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-zinc-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition">
                                    <a href="{{ route('events.show', $evt) }}" wire:navigate>{{ $evt->event_name }}</a>
                                </h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $evt->event_datetime->format('g:i A') }} &bull; {{ Str::limit($evt->description, 70) }}</p>
                            </div>
                        </div>

                        <flux:button variant="ghost" size="sm" :href="route('events.show', $evt)" icon="chevron-right" wire:navigate />
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
