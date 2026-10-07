<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-100 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased transition-colors duration-200">
        <div class="absolute top-4 right-4 flex items-center gap-2">
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
        </div>

        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium group" wire:navigate>
                    <div class="flex aspect-square size-12 items-center justify-center rounded-2xl bg-red-700 text-white shadow-lg ring-2 ring-amber-500/40 group-hover:scale-105 transition">
                        <span class="text-base font-black tracking-wider text-amber-300">MOT</span>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Marines of Tahlequah</span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">Semper Fidelis &bull; Community &bull; Veterans</span>
                </a>
                <div class="flex flex-col gap-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 sm:p-8 shadow-xl backdrop-blur-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
