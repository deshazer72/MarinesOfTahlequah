<?php

use App\Models\Event;
use Carbon\CarbonImmutable;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Events & Calendar - Marines of Tahlequah')]
#[Layout('layouts.public', ['title' => 'Events & Calendar - Marines of Tahlequah'])]
class extends Component {
    public string $view_month;
    public ?string $selected_date = null;
    public string $view_mode = 'calendar'; // 'calendar' or 'grid'
    public ?int $deletingEventId = null;

    public function mount(): void
    {
        $today = CarbonImmutable::today();
        $this->view_month = $today->startOfMonth()->toDateString();
        $this->selected_date = $today->toDateString();
    }

    public function previousMonth(): void
    {
        $this->view_month = CarbonImmutable::parse($this->view_month)->subMonth()->toDateString();
    }

    public function nextMonth(): void
    {
        $this->view_month = CarbonImmutable::parse($this->view_month)->addMonth()->toDateString();
    }

    public function goToToday(): void
    {
        $today = CarbonImmutable::today();
        $this->view_month = $today->startOfMonth()->toDateString();
        $this->selected_date = $today->toDateString();
    }

    public function selectDate(string $date): void
    {
        if ($this->selected_date === $date) {
            $this->selected_date = null;
        } else {
            $this->selected_date = $date;
        }
    }

    public function clearSelectedDate(): void
    {
        $this->selected_date = null;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingEventId = $id;
    }

    public function deleteEvent(): void
    {
        if (!auth()->check() || (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())) {
            abort(403);
        }

        if ($this->deletingEventId) {
            Event::findOrFail($this->deletingEventId)->delete();
            $this->deletingEventId = null;

            Flux::toast(
                text: __('Event deleted successfully.'),
                heading: __('Deleted'),
                variant: 'success',
            );
        }
    }

    public function with(): array
    {
        $today = CarbonImmutable::today();
        $monthStart = CarbonImmutable::parse($this->view_month)->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();
        $gridStart = $monthStart->startOfWeek(CarbonImmutable::SUNDAY);
        $gridEnd = $monthEnd->endOfWeek(CarbonImmutable::SATURDAY);

        $cells = [];
        $cursor = $gridStart;
        while ($cursor->lessThanOrEqualTo($gridEnd)) {
            $cells[] = $cursor;
            $cursor = $cursor->addDay();
        }

        $gridEvents = collect();
        $upcomingEvents = collect();
        $selectedDateEvents = collect();
        $allEvents = collect();
        $dbError = null;

        try {
            $gridEvents = Event::with(['creator', 'updater'])
                ->whereBetween('event_datetime', [
                    $gridStart->startOfDay(),
                    $gridEnd->endOfDay(),
                ])
                ->orderBy('event_datetime', 'asc')
                ->get();

            $upcomingEvents = Event::with(['creator', 'updater'])
                ->where('event_datetime', '>=', $today->startOfDay())
                ->orderBy('event_datetime', 'asc')
                ->get();

            if ($this->selected_date) {
                $selectedDateEvents = Event::with(['creator', 'updater'])
                    ->whereDate('event_datetime', $this->selected_date)
                    ->orderBy('event_datetime', 'asc')
                    ->get();
            }

            $allEvents = Event::with(['creator', 'updater'])
                ->orderBy('event_datetime', 'desc')
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Events page query error: ' . $e->getMessage());
            $dbError = $e->getMessage();
        }

        $eventsByDate = $gridEvents->groupBy(fn ($e) => $e->event_datetime->format('Y-m-d'));

        return [
            'month_label' => $monthStart->format('F Y'),
            'month_index' => $monthStart->month,
            'cells' => $cells,
            'today_string' => $today->toDateString(),
            'events_by_date' => $eventsByDate,
            'upcoming_events' => $upcomingEvents,
            'selected_date_events' => $selectedDateEvents,
            'all_events' => $allEvents,
            'isAdmin' => auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()),
            'dbError' => $dbError,
        ];
    }
}; ?>

<div class="py-10 md:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-10">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-400 mb-2">
                    <span>Community Calendar &amp; Gatherings</span>
                </div>
                <h1 class="text-4xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Events Calendar</h1>
                <p class="mt-2 text-zinc-600 dark:text-zinc-400 text-sm sm:text-base">
                    View upcoming Marine Corps ceremonies, fundraisers, community breakfasts, and raffles.
                </p>
            </div>

            <div class="flex items-center gap-3">
                {{-- View Toggle (Calendar / Grid) --}}
                <div class="inline-flex rounded-lg p-1 bg-zinc-200/80 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700">
                    <button
                        type="button"
                        wire:click="$set('view_mode', 'calendar')"
                        class="px-3 py-1.5 rounded-md text-xs font-semibold transition cursor-pointer {{ $view_mode === 'calendar' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}"
                    >
                        <span class="flex items-center gap-1.5">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                            Calendar View
                        </span>
                    </button>
                    <button
                        type="button"
                        wire:click="$set('view_mode', 'grid')"
                        class="px-3 py-1.5 rounded-md text-xs font-semibold transition cursor-pointer {{ $view_mode === 'grid' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white' }}"
                    >
                        <span class="flex items-center gap-1.5">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                            All Cards
                        </span>
                    </button>
                </div>

                @if($isAdmin)
                    <flux:button variant="primary" :href="route('events.create')" icon="plus" wire:navigate class="bg-red-700 hover:bg-red-600 text-white shadow-xs">
                        Create Event
                    </flux:button>
                @endif
            </div>
        </div>

        @if ($dbError)
            <div class="mb-8 rounded-xl border border-amber-500/40 bg-amber-500/10 p-4 text-sm text-amber-300">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <flux:icon.exclamation-triangle class="size-4" />
                    <span>Database Setup Required</span>
                </div>
                <p>The database tables have not been created yet. In your Laravel Cloud console, run: <code class="bg-black/30 px-2 py-0.5 rounded font-mono text-xs text-amber-200">php artisan migrate --force</code> followed by <code class="bg-black/30 px-2 py-0.5 rounded font-mono text-xs text-amber-200">php artisan db:seed --force</code>.</p>
            </div>
        @endif

        @if ($view_mode === 'calendar')
            {{-- Interactive Calendar + Upcoming Events View (like Carter Cabin) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Calendar Grid Column --}}
                <div class="lg:col-span-7 xl:col-span-7">
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 shadow-md dark:shadow-xl">
                        {{-- Calendar Header / Month Navigation --}}
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-bold text-zinc-900 dark:text-white tracking-tight">
                                    {{ $month_label }}
                                </h2>
                                <button
                                    type="button"
                                    wire:click="goToToday"
                                    class="text-xs font-semibold px-2.5 py-1 rounded-md bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition cursor-pointer"
                                >
                                    Today
                                </button>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    wire:click="previousMonth"
                                    class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition cursor-pointer"
                                    aria-label="Previous month"
                                >
                                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="nextMonth"
                                    class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition cursor-pointer"
                                    aria-label="Next month"
                                >
                                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Weekday Headers --}}
                        <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold text-zinc-400 dark:text-zinc-500 mb-2 uppercase tracking-wider">
                            <div>Sun</div>
                            <div>Mon</div>
                            <div>Tue</div>
                            <div>Wed</div>
                            <div>Thu</div>
                            <div>Fri</div>
                            <div>Sat</div>
                        </div>

                        {{-- Calendar Day Grid --}}
                        <div class="grid grid-cols-7 gap-1.5">
                            @foreach ($cells as $cell)
                                @php
                                    $dateStr = $cell->toDateString();
                                    $inMonth = $cell->month === $month_index;
                                    $isToday = $dateStr === $today_string;
                                    $isSelected = $selected_date && $dateStr === $selected_date;
                                    $dayEvents = $events_by_date->get($dateStr, collect());
                                    $hasEvents = $dayEvents->isNotEmpty();
                                @endphp

                                <button
                                    type="button"
                                    wire:click="selectDate('{{ $dateStr }}')"
                                    class="relative flex flex-col items-center justify-between min-h-[64px] sm:min-h-[72px] p-1.5 rounded-xl text-sm transition font-medium cursor-pointer border
                                        @if ($isSelected)
                                            bg-red-700 text-white border-red-600 shadow-md ring-2 ring-red-500/50
                                        @elseif ($hasEvents)
                                            bg-amber-50 dark:bg-amber-950/30 text-amber-950 dark:text-amber-200 border-amber-300 dark:border-amber-700/60 hover:border-amber-400
                                        @elseif ($isToday)
                                            bg-zinc-100 dark:bg-zinc-800/80 text-zinc-900 dark:text-white border-amber-500 dark:border-amber-400 font-bold
                                        @elseif (!$inMonth)
                                            bg-transparent text-zinc-300 dark:text-zinc-700 border-transparent hover:bg-zinc-50 dark:hover:bg-zinc-800/40
                                        @else
                                            bg-zinc-50/50 dark:bg-zinc-800/40 text-zinc-700 dark:text-zinc-300 border-zinc-200/60 dark:border-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:border-zinc-300
                                        @endif"
                                >
                                    {{-- Day Number --}}
                                    <div class="flex items-center justify-between w-full px-1">
                                        <span class="text-xs font-bold leading-tight {{ $isToday && !$isSelected ? 'text-amber-600 dark:text-amber-400' : '' }}">
                                            {{ $cell->day }}
                                        </span>
                                        @if ($isToday && !$isSelected)
                                            <span class="size-1.5 rounded-full bg-amber-500"></span>
                                        @endif
                                    </div>

                                    {{-- Event Indicators --}}
                                    @if ($hasEvents)
                                        <div class="w-full flex flex-col gap-0.5 mt-1">
                                            @foreach ($dayEvents->take(2) as $evt)
                                                <div class="w-full truncate text-[10px] px-1 py-0.5 rounded-md leading-none font-semibold text-left
                                                    @if ($isSelected)
                                                        bg-white/20 text-white
                                                    @else
                                                        bg-red-700 text-white dark:bg-red-800
                                                    @endif"
                                                    title="{{ $evt->event_name }}"
                                                >
                                                    {{ $evt->event_name }}
                                                </div>
                                            @endforeach
                                            @if ($dayEvents->count() > 2)
                                                <span class="text-[9px] font-bold opacity-80 leading-none">
                                                    +{{ $dayEvents->count() - 2 }} more
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="h-4"></div>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        {{-- Legend --}}
                        <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-wrap items-center gap-4">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="inline-block size-3 rounded-sm bg-red-700"></span> Selected Date
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="inline-block size-3 rounded-sm bg-amber-200 dark:bg-amber-900 border border-amber-400"></span> Has Event
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="inline-block size-3 rounded-sm border-2 border-amber-500"></span> Today
                                </span>
                            </div>
                            <span class="text-[11px] text-zinc-400">Click any day to view scheduled events</span>
                        </div>
                    </div>
                </div>

                {{-- Events Listing Column --}}
                <div class="lg:col-span-5 xl:col-span-5 space-y-6">
                    {{-- Selected Date or Upcoming Header --}}
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 shadow-md dark:shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                @if ($selected_date)
                                    <span class="text-xs uppercase font-bold tracking-wider text-amber-600 dark:text-amber-400">
                                        {{ CarbonImmutable::parse($selected_date)->format('l') }}
                                    </span>
                                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">
                                        {{ CarbonImmutable::parse($selected_date)->format('F j, Y') }}
                                    </h3>
                                @else
                                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">
                                        Upcoming Events
                                    </h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Next scheduled gatherings</p>
                                @endif
                            </div>

                            @if ($selected_date)
                                <button
                                    type="button"
                                    wire:click="clearSelectedDate"
                                    class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer"
                                >
                                    Show All Upcoming &rarr;
                                </button>
                            @endif
                        </div>

                        {{-- Events for Selected Date --}}
                        @if ($selected_date)
                            @if ($selected_date_events->isEmpty())
                                <div class="py-8 text-center">
                                    <div class="mx-auto mb-3 flex size-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No events scheduled on this day.</p>
                                    <p class="text-xs text-zinc-400 mt-1">Select another day or browse upcoming gatherings below.</p>

                                    @if ($isAdmin)
                                        <div class="mt-4">
                                            <flux:button variant="ghost" size="sm" :href="route('events.create')" icon="plus" wire:navigate class="text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40">
                                                Schedule Event on {{ CarbonImmutable::parse($selected_date)->format('M j') }}
                                            </flux:button>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach ($selected_date_events as $event)
                                        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/40 p-4 transition hover:border-zinc-300 dark:hover:border-zinc-700">
                                            @if ($event->image_url)
                                                <div class="relative w-full overflow-hidden rounded-lg bg-zinc-950 flex items-center justify-center min-h-[160px] max-h-[260px] mb-3 border border-zinc-200/60 dark:border-zinc-800" style="max-height: 260px;">
                                                    {{-- Ambient blurred backdrop to fill letterboxing seamlessly --}}
                                                    <img
                                                        src="{{ $event->image_url }}"
                                                        alt=""
                                                        aria-hidden="true"
                                                        class="absolute inset-0 w-full h-full object-cover filter blur-2xl opacity-40 scale-110 pointer-events-none select-none"
                                                    />
                                                    {{-- Crisp uncropped photo showing full subject and faces --}}
                                                    <img
                                                        src="{{ $event->image_url }}"
                                                        alt="{{ $event->event_name }}"
                                                        class="relative z-10 w-auto max-w-full max-h-[260px] object-contain shadow-md"
                                                        style="max-height: 260px;"
                                                        onerror="this.parentElement.style.display='none'"
                                                    />
                                                </div>
                                            @endif

                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="inline-flex items-center gap-1 rounded-md bg-red-100 dark:bg-red-950/60 px-2 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400">
                                                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $event->event_datetime->format('g:i A') }}
                                                </span>
                                            </div>

                                            <h4 class="text-base font-bold text-zinc-900 dark:text-white mb-1">
                                                <a href="{{ route('events.show', $event) }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition" wire:navigate>
                                                    {{ $event->event_name }}
                                                </a>
                                            </h4>

                                            <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-2 leading-relaxed mb-3">
                                                {{ $event->description }}
                                            </p>

                                            <div class="flex items-center justify-between pt-2 border-t border-zinc-200/60 dark:border-zinc-700/60">
                                                <flux:button variant="ghost" size="sm" :href="route('events.show', $event)" icon-trailing="arrow-right" wire:navigate class="text-xs text-amber-600 dark:text-amber-400">
                                                    View Details
                                                </flux:button>

                                                @if ($isAdmin)
                                                    <div class="flex items-center gap-1">
                                                        <flux:button variant="ghost" size="sm" :href="route('events.edit', $event)" icon="pencil-square" wire:navigate class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400" />
                                                        <flux:button variant="ghost" size="sm" wire:click="confirmDelete({{ $event->id }})" icon="trash" class="text-red-600 hover:text-red-700" />
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Next Upcoming Events Feed --}}
                    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 shadow-md dark:shadow-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-zinc-900 dark:text-white">All Upcoming Gatherings</h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $upcoming_events->count() }} event(s) on the calendar</p>
                            </div>
                        </div>

                        @if ($upcoming_events->isEmpty())
                            <div class="py-6 text-center text-xs text-zinc-500">
                                No upcoming events scheduled at this time.
                            </div>
                        @else
                            <div class="divide-y divide-zinc-200 dark:divide-zinc-800/80">
                                @foreach ($upcoming_events as $event)
                                    <div class="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            {{-- Mini Date Badge --}}
                                            <div class="shrink-0 text-center px-2.5 py-1 rounded-lg bg-red-100 dark:bg-red-950/60 border border-red-200 dark:border-red-800/40">
                                                <span class="block text-[10px] font-bold uppercase text-red-700 dark:text-red-400 leading-tight">{{ $event->event_datetime->format('M') }}</span>
                                                <span class="block text-base font-black text-zinc-900 dark:text-white leading-none">{{ $event->event_datetime->format('d') }}</span>
                                            </div>

                                            <div class="min-w-0">
                                                <h4 class="text-sm font-bold text-zinc-900 dark:text-white truncate hover:text-amber-600 dark:hover:text-amber-400">
                                                    <a href="{{ route('events.show', $event) }}" wire:navigate>{{ $event->event_name }}</a>
                                                </h4>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
                                                    {{ $event->event_datetime->format('g:i A') }} &bull; {{ Str::limit($event->description, 50) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            <flux:button variant="ghost" size="sm" :href="route('events.show', $event)" icon="chevron-right" wire:navigate />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            {{-- Traditional Cards Grid View --}}
            @if ($all_events->isEmpty())
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-12 text-center max-w-2xl mx-auto shadow-md">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-1">No Events Scheduled</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">Check back soon for upcoming gatherings and fundraisers!</p>
                    @if($isAdmin)
                        <flux:button variant="primary" :href="route('events.create')" icon="plus" wire:navigate class="bg-red-700 hover:bg-red-600 text-white">
                            Create First Event
                        </flux:button>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($all_events as $event)
                        <article class="flex flex-col justify-between overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-md hover:shadow-xl transition hover:border-zinc-300 dark:hover:border-zinc-700 hover:scale-[1.01]">
                            <div>
                                @if ($event->image_url)
                                    <div class="relative w-full overflow-hidden bg-zinc-950 flex items-center justify-center min-h-[220px] max-h-[340px] border-b border-zinc-200 dark:border-zinc-800" style="max-height: 340px;">
                                        {{-- Ambient blurred backdrop to fill letterboxing seamlessly --}}
                                        <img
                                            src="{{ $event->image_url }}"
                                            alt=""
                                            aria-hidden="true"
                                            class="absolute inset-0 w-full h-full object-cover filter blur-2xl opacity-40 scale-110 pointer-events-none select-none"
                                        />
                                        {{-- Crisp uncropped photo showing full subject and faces --}}
                                        <img
                                            src="{{ $event->image_url }}"
                                            alt="{{ $event->event_name }}"
                                            class="relative z-10 w-auto max-w-full max-h-[340px] object-contain shadow-xl"
                                            style="max-height: 340px;"
                                            onerror="this.parentElement.style.display='none'"
                                        />
                                    </div>
                                @else
                                    <div class="h-32 w-full flex items-center justify-center bg-zinc-100 dark:bg-zinc-800/40 border-b border-zinc-200 dark:border-zinc-800">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Marines of Tahlequah</span>
                                    </div>
                                @endif

                                <div class="p-6">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-red-100 dark:bg-red-950/60 border border-red-200 dark:border-red-800/60 px-2.5 py-1 text-xs font-semibold text-red-700 dark:text-red-400">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $event->event_datetime->format('M j, Y • g:i A') }}
                                        </span>
                                    </div>

                                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-2 line-clamp-2">
                                        <a href="{{ route('events.show', $event) }}" class="hover:text-amber-600 dark:hover:text-amber-400 transition" wire:navigate>
                                            {{ $event->event_name }}
                                        </a>
                                    </h2>

                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-3 leading-relaxed mb-4">
                                        {{ $event->description }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-6 pt-0 border-t border-zinc-100 dark:border-zinc-800/60 mt-4 flex items-center justify-between">
                                <flux:button variant="ghost" size="sm" :href="route('events.show', $event)" icon-trailing="arrow-right" wire:navigate class="text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300">
                                    View Details
                                </flux:button>

                                @if ($isAdmin)
                                    <div class="flex items-center gap-1">
                                        <flux:button variant="ghost" size="sm" :href="route('events.edit', $event)" icon="pencil-square" wire:navigate class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200" />
                                        <flux:button variant="ghost" size="sm" wire:click="confirmDelete({{ $event->id }})" icon="trash" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" />
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- Delete Confirmation Modal --}}
        @if ($deletingEventId)
            <div wire:key="delete-event-modal-{{ $deletingEventId }}" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                <div class="flex min-h-screen items-center justify-center p-4 text-center">
                    {{-- Backdrop --}}
                    <div class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity" wire:click="$set('deletingEventId', null)"></div>

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
                                wire:click="$set('deletingEventId', null)"
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
