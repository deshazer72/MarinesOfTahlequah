<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('home') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Community')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" :href="route('events.index')" :current="request()->routeIs('events.*') && !request()->routeIs('events.create') && !request()->routeIs('events.edit')" wire:navigate>
                        {{ __('Events') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="information-circle" :href="route('about.index')" :current="request()->routeIs('about.*') && !request()->routeIs('about.create') && !request()->routeIs('about.edit')" wire:navigate>
                        {{ __('About Us') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="heart" :href="route('donate')" :current="request()->routeIs('donate')" wire:navigate>
                        {{ __('Donate') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                    <flux:sidebar.group :heading="__('Management')" class="grid">
                        <flux:sidebar.item icon="plus-circle" :href="route('events.create')" :current="request()->routeIs('events.create')" wire:navigate>
                            {{ __('Add Event') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-plus" :href="route('about.create')" :current="request()->routeIs('about.create')" wire:navigate>
                            {{ __('Add About') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="photo" :href="route('admin.slideshow')" :current="request()->routeIs('admin.slideshow')" wire:navigate>
                            {{ __('Slideshow') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                @if(auth()->check() && auth()->user()->isSuperAdmin())
                    <flux:sidebar.group :heading="__('Administration')" class="grid">
                        <flux:sidebar.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                            {{ __('User Roles') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item
                    data-theme-menu-light
                    onclick="window.toggleTheme()"
                    icon="sun"
                    class="cursor-pointer text-amber-500 hover:text-amber-400"
                >
                    {{ __('Light Mode') }}
                </flux:sidebar.item>
                <flux:sidebar.item
                    data-theme-menu-dark
                    onclick="window.toggleTheme()"
                    icon="moon"
                    class="cursor-pointer text-zinc-600 hover:text-zinc-900"
                >
                    {{ __('Dark Mode') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="globe-alt" :href="route('home')" wire:navigate>
                    {{ __('Back to Website') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="lg:hidden border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <button
                type="button"
                data-theme-switch
                onclick="window.toggleTheme()"
                class="relative inline-flex h-8 w-14 shrink-0 cursor-pointer items-center rounded-full border-2 bg-zinc-800 border-zinc-700 transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-amber-500/50 mr-2"
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

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <div class="flex items-center gap-2 px-2 py-1.5 text-start text-sm">
                        <flux:avatar
                            :name="auth()->user()->name"
                            :initials="auth()->user()->initials()"
                        />

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                        </div>
                    </div>

                    <flux:menu.separator />

                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer text-red-600 dark:text-red-400"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        <flux:toast />
        @fluxScripts
    </body>
</html>
