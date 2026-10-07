<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Donate - Marines of Tahlequah')]
#[Layout('layouts.public', ['title' => 'Donate - Marines of Tahlequah'])]
class extends Component {
    //
}; ?>

<div class="py-12 md:py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-semibold text-amber-400 mb-3">
                <span>Support Our Mission</span>
            </div>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Donate</h1>
            <p class="mt-4 text-base sm:text-lg text-zinc-600 dark:text-zinc-300 max-w-2xl mx-auto">
                At the Marines of Tahlequah, we appreciate your generosity. Every dollar goes directly to help local veterans, families, and community relief.
            </p>
        </div>

        {{-- Donation Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            {{-- PayPal Card --}}
            <div class="flex flex-col items-center rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-8 shadow-xl text-center hover:border-amber-600/50 transition">
                <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-blue-600/20 text-blue-500 dark:text-blue-400">
                    <svg class="size-7" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.31-.02.13-.046.264-.077.402-.68 3.018-2.678 4.71-5.753 4.71h-2.12c-.52 0-.96.38-1.04.898l-1.094 6.942-.09.57a.641.641 0 0 1-.633.535l1.723-10.93h2.254c2.457 0 4.103-1.378 4.673-3.905.244-1.58-.02-2.584-.798-3.468-.846-.962-2.39-1.38-4.475-1.38H7.135a.641.641 0 0 0-.633.543L3.89 20.697h3.186z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">PayPal</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">Scan with your phone's camera or PayPal app</p>

                <div class="rounded-xl bg-white p-4 shadow-inner ring-1 ring-zinc-200">
                    <img
                        src="/QR codes/705.png"
                        alt="PayPal QR Code for Marines of Tahlequah"
                        class="size-48 object-contain"
                    />
                </div>

                <div class="mt-6">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 dark:bg-blue-900/30 px-3 py-1 text-xs font-medium text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40">
                        Secure PayPal Processing
                    </span>
                </div>
            </div>

            {{-- Cash App Card --}}
            <div class="flex flex-col items-center rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-8 shadow-xl text-center hover:border-green-600/50 transition">
                <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-emerald-600/20 text-emerald-600 dark:text-emerald-400">
                    <svg class="size-7" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.623 5.367 11.99 11.988 11.99 6.62 0 11.987-5.367 11.987-11.99C24.004 5.367 18.637 0 12.017 0zm2.22 17.067l-.48 2.017-1.748-.002.486-2.028c-.682-.128-1.393-.385-2.029-.757l.745-1.921c.563.344 1.258.604 1.938.604.832 0 1.341-.355 1.341-.951 0-.62-.519-.948-1.579-1.428-1.57-.702-2.317-1.487-2.317-2.738 0-1.472 1.09-2.584 2.802-2.884l.43-1.815 1.748.002-.437 1.838c.602.109 1.156.31 1.638.583l-.693 1.879c-.482-.266-.99-.449-1.564-.449-.806 0-1.236.37-1.236.853 0 .524.453.829 1.488 1.29 1.706.758 2.408 1.584 2.408 2.868 0 1.536-1.077 2.685-2.928 3.018z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">Cash App</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">
                    Scan with Cash App or send to <span class="font-bold text-emerald-600 dark:text-emerald-400">$MarinesofTahlequah</span>
                </p>

                <div class="rounded-xl bg-white p-4 shadow-inner ring-1 ring-zinc-200">
                    <img
                        src="/QR codes/706.png"
                        alt="Cash App QR Code for Marines of Tahlequah"
                        class="size-48 object-contain"
                    />
                </div>

                <div class="mt-6">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">
                        Cashtag: $MarinesofTahlequah
                    </span>
                </div>
            </div>
        </div>

        {{-- Impact Badges --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white/60 dark:bg-zinc-900/40 p-6 text-center">
            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-zinc-700 dark:text-zinc-300">
                <span class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-emerald-500"></span>
                    100% Dedicated to Veterans & Community
                </span>
                <span class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-blue-500"></span>
                    Secure & Verified Donations
                </span>
                <span class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-amber-500"></span>
                    Local Tahlequah Impact
                </span>
            </div>
        </div>
    </div>
</div>
