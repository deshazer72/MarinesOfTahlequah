<?php

use App\Models\Event;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Event Details - Marines of Tahlequah')]
#[Layout('layouts.public', ['title' => 'Event Details - Marines of Tahlequah'])]
class extends Component {
    public Event $event;
    public bool $confirmingDelete = false;

    public function mount(Event $event): void
    {
        $this->event = $event->load(['creator', 'updater']);
    }

    public function deleteEvent(): void
    {
        if (!auth()->check() || (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())) {
            abort(403);
        }

        $this->event->delete();

        Flux::toast(
            text: __('Event deleted successfully.'),
            heading: __('Deleted'),
            variant: 'success',
        );

        $this->redirect(route('events.index'), navigate: true);
    }
}; ?>

<div class="py-12 md:py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        {{-- Navigation & Actions --}}
        <div class="flex items-center justify-between mb-8">
            <flux:button variant="ghost" :href="route('events.index')" icon="arrow-left" wire:navigate>
                Back to Events
            </flux:button>

            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                <div class="flex items-center gap-2">
                    <flux:button variant="ghost" :href="route('events.edit', $event)" icon="pencil-square" wire:navigate>
                        Edit Event
                    </flux:button>
                    <flux:button variant="danger" wire:click="$set('confirmingDelete', true)" icon="trash">
                        Delete
                    </flux:button>
                </div>
            @endif
        </div>

        <article class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-xl dark:shadow-2xl">
            @if ($event->image_url)
                <div class="relative w-full overflow-hidden bg-zinc-950 flex items-center justify-center min-h-[300px] max-h-[580px] border-b border-zinc-200 dark:border-zinc-800" style="max-height: 580px;">
                    {{-- Ambient blurred backdrop --}}
                    <img
                        src="{{ $event->image_url }}"
                        alt=""
                        aria-hidden="true"
                        class="absolute inset-0 w-full h-full object-cover filter blur-2xl opacity-40 scale-110 pointer-events-none select-none"
                        onerror="this.style.display='none'"
                    />
                    {{-- Full uncropped photo --}}
                    <img
                        src="{{ $event->image_url }}"
                        alt="{{ $event->event_name }}"
                        class="relative z-10 w-auto max-w-full max-h-[580px] object-contain shadow-2xl"
                        style="max-height: 580px;"
                        onerror="this.closest('.relative').style.display='none'"
                    />
                </div>
            @endif

            <div class="p-6 sm:p-10">
                <div class="inline-flex items-center gap-2 rounded-lg bg-red-100 dark:bg-red-950/70 border border-red-200 dark:border-red-800/60 px-3.5 py-1.5 text-sm font-semibold text-red-700 dark:text-red-400 mb-4">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $event->event_datetime->format('l, F j, Y \a\t g:i A') }}
                </div>

                <h1 class="text-3xl sm:text-4xl font-extrabold text-zinc-900 dark:text-white tracking-tight mb-6">
                    {{ $event->event_name }}
                </h1>

                <div class="text-zinc-700 dark:text-zinc-300 text-lg leading-relaxed whitespace-pre-line mb-8">
                    {{ $event->description }}
                </div>

                <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800 flex flex-wrap items-center justify-between text-xs text-zinc-500 gap-4">
                    <div>
                        @if ($event->creator)
                            <span>Created by {{ $event->creator->name }} on {{ $event->created_at->format('M j, Y') }}</span>
                        @endif
                    </div>
                    @if ($event->updater && $event->updated_at != $event->created_at)
                        <span>Last updated by {{ $event->updater->name }} on {{ $event->updated_at->format('M j, Y') }}</span>
                    @endif
                </div>
            </div>
        </article>

        {{-- Delete Confirmation Modal --}}
        @if ($confirmingDelete)
            <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                <div class="flex min-h-screen items-center justify-center p-4 text-center">
                    {{-- Backdrop --}}
                    <div class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity" wire:click="$set('confirmingDelete', false)"></div>

                    {{-- Dialog Box --}}
                    <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-2xl text-zinc-900 dark:text-zinc-100 z-10 text-left">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex size-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/70 text-red-600 dark:text-red-400 shrink-0">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('Delete Event') }}</h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</p>
                            </div>
                        </div>

                        <p class="text-sm text-zinc-600 dark:text-zinc-300 mb-6">
                            {{ __('Are you sure you want to permanently delete this event?') }}
                        </p>

                        <div class="flex justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                            <button
                                type="button"
                                wire:click="$set('confirmingDelete', false)"
                                class="px-4 py-2 text-sm font-medium rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="button"
                                wire:click="deleteEvent"
                                class="px-5 py-2 text-sm font-semibold rounded-xl bg-red-700 hover:bg-red-600 text-white shadow-md transition cursor-pointer"
                            >
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
