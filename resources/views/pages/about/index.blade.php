<?php

use App\Models\About;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('About Us - Marines of Tahlequah')]
#[Layout('layouts.public', ['title' => 'About Us - Marines of Tahlequah'])]
class extends Component {
    public ?int $deletingAboutId = null;

    public function with(): array
    {
        $isAdmin = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin());

        return [
            'aboutItems' => About::with('user')
                ->when(! $isAdmin, fn ($q) => $q->where('is_published', true))
                ->orderBy('order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get(),
            'isAdmin' => $isAdmin,
        ];
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingAboutId = $id;
    }

    public function deleteAbout(): void
    {
        if (!auth()->check() || (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())) {
            abort(403);
        }

        if ($this->deletingAboutId) {
            About::findOrFail($this->deletingAboutId)->delete();
            $this->deletingAboutId = null;

            Flux::toast(
                text: __('Content deleted successfully.'),
                heading: __('Deleted'),
                variant: 'success',
            );
        }
    }
}; ?>

<div class="py-12 md:py-20">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-12">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-semibold text-amber-400 mb-2">
                    <span>Our Story & Mission</span>
                </div>
                <h1 class="text-4xl font-extrabold text-zinc-900 dark:text-white tracking-tight">About Marines of Tahlequah</h1>
            </div>

            @if($isAdmin)
                <flux:button variant="primary" :href="route('about.create')" icon="plus" wire:navigate class="bg-red-700 hover:bg-red-600 text-white shadow-xs">
                    Add About Content
                </flux:button>
            @endif
        </div>

        {{-- Content List --}}
        @if ($aboutItems->isEmpty())
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white/60 dark:bg-zinc-900/50 p-12 text-center">
                <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-1">No About Content Yet</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 max-w-sm mx-auto mb-6">Content about Marines of Tahlequah will appear here once published.</p>
                @if($isAdmin)
                    <flux:button variant="primary" :href="route('about.create')" icon="plus" wire:navigate class="bg-red-700 hover:bg-red-600 text-white shadow-xs">
                        Create First Entry
                    </flux:button>
                @endif
            </div>
        @else
            <div class="space-y-8">
                @foreach ($aboutItems as $item)
                    <article class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-xl transition hover:border-zinc-300 dark:hover:border-zinc-700">
                        @if ($item->image_url)
                            <div class="relative w-full overflow-hidden bg-zinc-950 flex items-center justify-center min-h-[300px] max-h-[580px] border-b border-zinc-200 dark:border-zinc-800" style="max-height: 580px;">
                                {{-- Ambient blurred backdrop to fill letterboxing seamlessly --}}
                                <img
                                    src="{{ $item->image_url }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="absolute inset-0 w-full h-full object-cover filter blur-2xl opacity-40 scale-110 pointer-events-none select-none"
                                />
                                {{-- Crisp uncropped photo showing full subject and faces --}}
                                <img
                                    src="{{ $item->image_url }}"
                                    alt="{{ $item->title }}"
                                    class="relative z-10 w-auto max-w-full max-h-[580px] object-contain shadow-2xl"
                                    style="max-height: 580px;"
                                    onerror="this.parentElement.style.display='none'"
                                />
                            </div>
                        @endif

                        <div class="p-6 sm:p-8">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <h2 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">
                                        {{ $item->title }}
                                    </h2>
                                    @if (! $item->is_published)
                                        <flux:badge color="zinc" size="sm">Draft / Unpublished</flux:badge>
                                    @endif
                                </div>

                                @if ($isAdmin)
                                    <div class="flex items-center gap-2">
                                        <flux:button variant="ghost" size="sm" :href="route('about.edit', $item)" icon="pencil-square" wire:navigate class="text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white">
                                            Edit
                                        </flux:button>
                                        <flux:button variant="danger" size="sm" wire:click="confirmDelete({{ $item->id }})" icon="trash">
                                            Delete
                                        </flux:button>
                                    </div>
                                @endif
                            </div>

                            <div class="text-zinc-700 dark:text-zinc-300 text-base leading-relaxed whitespace-pre-line">
                                {{ $item->content }}
                            </div>

                            @if ($item->user)
                                <div class="mt-6 pt-4 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between text-xs text-zinc-500">
                                    <span>Posted by {{ $item->user->name }}</span>
                                    <time datetime="{{ $item->created_at->toISOString() }}">{{ $item->created_at->format('M j, Y') }}</time>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        {{-- Delete Confirmation Modal --}}
        @if ($deletingAboutId)
            <div wire:key="delete-about-modal-{{ $deletingAboutId }}" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                <div class="flex min-h-screen items-center justify-center p-4 text-center">
                    {{-- Backdrop --}}
                    <div class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity" wire:click="$set('deletingAboutId', null)"></div>

                    {{-- Dialog Box --}}
                    <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-2xl text-zinc-900 dark:text-zinc-100 z-10 text-left">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex size-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/70 text-red-600 dark:text-red-400 shrink-0">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('Delete About Content') }}</h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</p>
                            </div>
                        </div>

                        <p class="text-sm text-zinc-600 dark:text-zinc-300 mb-6">
                            {{ __('Are you sure you want to delete this section from the About page?') }}
                        </p>

                        <div class="flex justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                            <button
                                type="button"
                                wire:click="$set('deletingAboutId', null)"
                                class="px-4 py-2 text-sm font-medium rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="button"
                                wire:click="deleteAbout"
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
