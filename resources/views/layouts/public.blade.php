<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-100 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased flex flex-col justify-between selection:bg-red-800 selection:text-white transition-colors duration-200">
    {{-- Header / Navbar --}}
    <header class="sticky top-0 z-40 w-full border-b border-zinc-200/80 dark:border-zinc-800/80 bg-white/95 dark:bg-zinc-950/90 backdrop-blur-md transition-colors duration-200">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            {{-- Brand Logo --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-3 transition hover:opacity-90" wire:navigate>
                    <div class="flex aspect-square size-10 items-center justify-center rounded-xl bg-red-700 text-white shadow-md ring-1 ring-amber-500/40">
                        <span class="text-sm font-black tracking-wider text-amber-300">MOT</span>
                    </div>
                    <div>
                        <span class="font-bold text-lg tracking-tight text-zinc-900 dark:text-zinc-100 block leading-tight">Marines of Tahlequah</span>
                        <span class="text-xs text-amber-600 dark:text-amber-400 font-medium block">Semper Fidelis &bull; Community &bull; Veterans</span>
                    </div>
                </a>
            </div>

            {{-- Desktop Navigation --}}
            <nav class="hidden md:flex items-center gap-1.5">
                <a
                    href="{{ route('home') }}"
                    wire:navigate
                    class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('home') ? 'text-amber-600 dark:text-amber-400 font-semibold bg-amber-500/10 dark:bg-zinc-900/90 shadow-sm ring-1 ring-amber-500/30 dark:ring-zinc-700/60' : 'text-zinc-700 hover:text-amber-600 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:text-amber-400 dark:hover:bg-zinc-800/60' }}"
                >
                    Home
                </a>
                <a
                    href="{{ route('about.index') }}"
                    wire:navigate
                    class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('about.*') ? 'text-amber-600 dark:text-amber-400 font-semibold bg-amber-500/10 dark:bg-zinc-900/90 shadow-sm ring-1 ring-amber-500/30 dark:ring-zinc-700/60' : 'text-zinc-700 hover:text-amber-600 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:text-amber-400 dark:hover:bg-zinc-800/60' }}"
                >
                    About Us
                </a>
                <a
                    href="{{ route('events.index') }}"
                    wire:navigate
                    class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('events.*') && !request()->routeIs('events.create') && !request()->routeIs('events.edit') ? 'text-amber-600 dark:text-amber-400 font-semibold bg-amber-500/10 dark:bg-zinc-900/90 shadow-sm ring-1 ring-amber-500/30 dark:ring-zinc-700/60' : 'text-zinc-700 hover:text-amber-600 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:text-amber-400 dark:hover:bg-zinc-800/60' }}"
                >
                    Events
                </a>
                <a
                    href="{{ route('donate') }}"
                    wire:navigate
                    class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('donate') ? 'text-amber-600 dark:text-amber-400 font-semibold bg-amber-500/10 dark:bg-zinc-900/90 shadow-sm ring-1 ring-amber-500/30 dark:ring-zinc-700/60' : 'text-zinc-700 hover:text-amber-600 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:text-amber-400 dark:hover:bg-zinc-800/60' }}"
                >
                    Donate
                </a>

                @auth
                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        <a
                            href="{{ route('dashboard') }}"
                            wire:navigate
                            class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs('dashboard') || request()->routeIs('admin.*') || request()->routeIs('users.*') ? 'text-amber-600 dark:text-amber-400 font-semibold bg-amber-500/10 dark:bg-zinc-900/90 shadow-sm ring-1 ring-amber-500/30 dark:ring-zinc-700/60' : 'text-zinc-700 hover:text-amber-600 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:text-amber-400 dark:hover:bg-zinc-800/60' }}"
                        >
                            Admin
                        </a>
                    @endif
                @endauth
            </nav>

            {{-- User / Auth CTA & Theme Switcher --}}
            <div class="hidden md:flex items-center gap-3">
                {{-- Light / Dark Mode Toggle Switch --}}
                <button
                    type="button"
                    data-theme-switch
                    onclick="window.toggleTheme()"
                    class="relative inline-flex h-8 w-14 shrink-0 cursor-pointer items-center rounded-full border-2 bg-zinc-800 border-zinc-700 transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-amber-500/50"
                    role="switch"
                    aria-checked="true"
                    title="Switch to Light Mode"
                    aria-label="Switch to Light Mode"
                >
                    <span class="sr-only">Toggle Light/Dark Mode</span>
                    <span
                        data-theme-knob
                        class="pointer-events-none relative inline-flex size-6.5 translate-x-6 bg-zinc-950 text-amber-400 transform items-center justify-center rounded-full shadow-md transition-transform duration-200 ease-in-out"
                    >
                        <svg data-icon-sun class="size-4 shrink-0" style="display: none;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                        </svg>
                        <svg data-icon-moon class="size-3.5 shrink-0" style="display: inline-block;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                        </svg>
                    </span>
                </button>

                @auth
                    <flux:button variant="primary" :href="route('dashboard')" icon="home" wire:navigate class="bg-red-700 hover:bg-red-600 text-white text-sm font-semibold shadow-xs">
                        Dashboard
                    </flux:button>

                    <flux:dropdown position="bottom" align="end">
                        <flux:profile
                            :initials="auth()->user()->initials()"
                            icon-trailing="chevron-down"
                        />

                        <flux:menu class="bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <div class="px-2 py-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-200 block">{{ auth()->user()->name }}</span>
                                <span class="block truncate">{{ auth()->user()->email }}</span>
                            </div>
                            <flux:menu.separator />
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                {{ __('Settings') }}
                            </flux:menu.item>
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item
                                    as="button"
                                    type="submit"
                                    icon="arrow-right-start-on-rectangle"
                                    class="w-full cursor-pointer text-red-600 dark:text-red-400"
                                >
                                    {{ __('Log out') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                @else
                    <flux:button variant="ghost" :href="route('login')" wire:navigate class="text-sm font-medium text-zinc-700 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:text-white dark:hover:bg-zinc-800/60">
                        Log in
                    </flux:button>
                    <flux:button variant="primary" :href="route('register')" wire:navigate class="bg-red-700 hover:bg-red-600 text-white text-sm font-semibold shadow-md">
                        Register
                    </flux:button>
                @endauth
            </div>

            {{-- Mobile Menu Trigger & Theme Switcher --}}
            <div class="md:hidden flex items-center gap-2">
                {{-- Mobile Light / Dark Switcher --}}
                <button
                    type="button"
                    data-theme-switch
                    onclick="window.toggleTheme()"
                    class="relative inline-flex h-8 w-14 shrink-0 cursor-pointer items-center rounded-full border-2 bg-zinc-800 border-zinc-700 transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-amber-500/50"
                    role="switch"
                    aria-checked="true"
                    title="Switch to Light Mode"
                    aria-label="Switch to Light Mode"
                >
                    <span class="sr-only">Toggle Light/Dark Mode</span>
                    <span
                        data-theme-knob
                        class="pointer-events-none relative inline-flex size-6.5 translate-x-6 bg-zinc-950 text-amber-400 transform items-center justify-center rounded-full shadow-md transition-transform duration-200 ease-in-out"
                    >
                        <svg data-icon-sun class="size-4 shrink-0" style="display: none;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                        </svg>
                        <svg data-icon-moon class="size-3.5 shrink-0" style="display: inline-block;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                        </svg>
                    </span>
                </button>

                <flux:dropdown position="bottom" align="end">
                    <flux:button variant="ghost" icon="bars-3" aria-label="Open Navigation Menu" class="text-zinc-700 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:text-amber-400 dark:hover:bg-zinc-800/60" />

                    <flux:menu class="w-56 bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-900 dark:text-zinc-100">
                        <flux:menu.item :href="route('home')" icon="home" wire:navigate class="hover:text-amber-600 dark:hover:text-amber-400 {{ request()->routeIs('home') ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-zinc-700 dark:text-zinc-200' }}">Home</flux:menu.item>
                        <flux:menu.item :href="route('about.index')" icon="information-circle" wire:navigate class="hover:text-amber-600 dark:hover:text-amber-400 {{ request()->routeIs('about.*') ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-zinc-700 dark:text-zinc-200' }}">About Us</flux:menu.item>
                        <flux:menu.item :href="route('events.index')" icon="calendar" wire:navigate class="hover:text-amber-600 dark:hover:text-amber-400 {{ request()->routeIs('events.*') ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-zinc-700 dark:text-zinc-200' }}">Events</flux:menu.item>
                        <flux:menu.item :href="route('donate')" icon="heart" wire:navigate class="hover:text-amber-600 dark:hover:text-amber-400 {{ request()->routeIs('donate') ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-zinc-700 dark:text-zinc-200' }}">Donate</flux:menu.item>
                        @auth
                            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                <flux:menu.item :href="route('dashboard')" icon="home" wire:navigate class="hover:text-amber-600 dark:hover:text-amber-400 {{ request()->routeIs('dashboard') || request()->routeIs('admin.*') ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-zinc-700 dark:text-zinc-200' }}">Admin</flux:menu.item>
                            @endif
                        @endauth

                        <flux:menu.separator />

                        <div>
                            <flux:menu.item
                                as="button"
                                type="button"
                                data-theme-menu-light
                                onclick="window.toggleTheme()"
                                icon="sun"
                                class="w-full text-start text-amber-500 cursor-pointer"
                            >
                                {{ __('Switch to Light Mode') }}
                            </flux:menu.item>
                            <flux:menu.item
                                as="button"
                                type="button"
                                data-theme-menu-dark
                                onclick="window.toggleTheme()"
                                icon="moon"
                                class="w-full text-start text-zinc-700 cursor-pointer"
                            >
                                {{ __('Switch to Dark Mode') }}
                            </flux:menu.item>
                        </div>

                        @auth
                            <flux:menu.separator />
                            <flux:menu.item :href="route('dashboard')" icon="home" wire:navigate>Dashboard</flux:menu.item>

                            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                <flux:menu.item :href="route('events.create')" icon="plus-circle" wire:navigate>Add Event</flux:menu.item>
                                <flux:menu.item :href="route('about.create')" icon="document-plus" wire:navigate>Add About</flux:menu.item>
                                <flux:menu.item :href="route('admin.slideshow')" icon="photo" wire:navigate>Slideshow</flux:menu.item>
                            @endif

                            @if(auth()->user()->isSuperAdmin())
                                <flux:menu.item :href="route('users.index')" icon="users" wire:navigate>User Roles</flux:menu.item>
                            @endif

                            <flux:menu.separator />
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>Settings</flux:menu.item>
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full text-red-600 dark:text-red-400">
                                    Log out
                                </flux:menu.item>
                            </form>
                        @else
                            <flux:menu.separator />
                            <flux:menu.item :href="route('login')" icon="arrow-right-end-on-rectangle" wire:navigate>Log in</flux:menu.item>
                            <flux:menu.item :href="route('register')" icon="user-plus" wire:navigate>Register</flux:menu.item>
                        @endauth
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 mt-16 transition-colors duration-200">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                {{-- Left: Mission & Brand --}}
                <div class="flex flex-col items-center md:items-start text-center md:text-left gap-2 max-w-md">
                    <div class="flex items-center gap-2">
                        <div class="flex aspect-square size-7 items-center justify-center rounded-lg bg-red-700 text-white ring-1 ring-amber-500/40">
                            <span class="text-[10px] font-black tracking-wider text-amber-300">MOT</span>
                        </div>
                        <span class="font-bold text-base text-zinc-900 dark:text-zinc-100">Marines of Tahlequah</span>
                    </div>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Dedicated advocates for service members, veterans, and their families since 2015. Serving the Tahlequah and Cherokee County community with honor and commitment.
                    </p>
                </div>

                {{-- Center: Links --}}
                <nav class="flex flex-wrap items-center justify-center gap-6 text-sm text-zinc-600 dark:text-zinc-400">
                    <a href="{{ route('home') }}" class="transition hover:text-amber-600 dark:hover:text-amber-400" wire:navigate>Home</a>
                    <a href="{{ route('about.index') }}" class="transition hover:text-amber-600 dark:hover:text-amber-400" wire:navigate>About Us</a>
                    <a href="{{ route('events.index') }}" class="transition hover:text-amber-600 dark:hover:text-amber-400" wire:navigate>Events</a>
                    <a href="{{ route('donate') }}" class="transition hover:text-amber-600 dark:hover:text-amber-400 {{ request()->routeIs('donate') ? 'text-amber-600 dark:text-amber-400 font-semibold' : '' }}" wire:navigate>Donate</a>
                </nav>

                {{-- Right: Credit & Contact --}}
                <div class="flex flex-col items-center md:items-end text-center md:text-right text-xs text-zinc-500 gap-1">
                    <p>Created by <span class="font-medium text-zinc-700 dark:text-zinc-300">Mid-Tech Software Consulting</span></p>
                    <p>Contact: <a href="mailto:jdeshazer@mid-tech.net" class="text-amber-600 dark:text-amber-400 hover:underline">jdeshazer@mid-tech.net</a></p>
                    <p class="text-[11px] text-zinc-400 dark:text-zinc-600 mt-1">&copy; {{ date('Y') }} Marines of Tahlequah. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <flux:toast />
    @fluxScripts
</body>
</html>
