<?php

use App\Models\BirthdayAttendee;
use App\Models\SiteSetting;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

new
#[Title('Birthday Surveys & Raffle Tracker - Marines of Tahlequah')]
#[Layout('layouts.app')]
class extends Component {
    use WithPagination;

    public bool $surveyEnabled = false;
    public string $search = '';
    public string $filterYear = '';
    public string $filterType = '';
    public string $filterRaffle = '';
    public string $filterAttendance = '';

    // View Details / Edit Raffle Winner Modal
    public ?int $selectedAttendeeId = null;
    public bool $editHasWonRaffle = false;
    public string $editRafflePrizeWon = '';

    // Delete Modal
    public ?int $deletingId = null;
    public string $deletingName = '';

    public function mount(): void
    {
        if (!Auth::check() || (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin())) {
            abort(403);
        }

        $this->surveyEnabled = SiteSetting::isBirthdaySurveyEnabled();
    }

    public function toggleSurveyStatus(): void
    {
        $this->surveyEnabled = !$this->surveyEnabled;
        SiteSetting::setBirthdaySurveyEnabled($this->surveyEnabled);

        Flux::toast(
            text: $this->surveyEnabled
                ? __('Birthday Survey is now ON and visible to the public.')
                : __('Birthday Survey is now OFF and hidden from the website.'),
            heading: $this->surveyEnabled ? __('Survey Enabled') : __('Survey Disabled'),
            variant: $this->surveyEnabled ? 'success' : 'warning',
        );
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterYear(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRaffle(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAttendance(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterYear', 'filterType', 'filterRaffle', 'filterAttendance']);
        $this->resetPage();
    }

    protected function buildQuery()
    {
        $query = BirthdayAttendee::query();

        if ($this->filterYear !== '') {
            $query->where('event_year', (int) $this->filterYear);
        }

        if ($this->search !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('middle_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('address', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('branch', 'like', $term)
                    ->orWhere('rank', 'like', $term)
                    ->orWhere('honoring_marine_name', 'like', $term)
                    ->orWhere('raffle_prize_won', 'like', $term)
                    ->orWhere('donation_details', 'like', $term)
                    ->orWhere('suggestions', 'like', $term);
            });
        }

        if ($this->filterType !== '') {
            if ($this->filterType === 'donor') {
                $query->where('is_donor', true);
            } elseif ($this->filterType === 'honor') {
                $query->where('attending_in_honor', true);
            } else {
                $query->where('attendee_type', $this->filterType);
            }
        }

        if ($this->filterRaffle === 'never_won') {
            $query->where('participates_in_raffles', true)->where('has_won_raffle', false);
        } elseif ($this->filterRaffle === 'won') {
            $query->where('has_won_raffle', true);
        } elseif ($this->filterRaffle === 'participates') {
            $query->where('participates_in_raffles', true);
        }

        if ($this->filterAttendance === 'first_time') {
            $query->where('attended_before', false);
        } elseif ($this->filterAttendance === 'returning') {
            $query->where('attended_before', true);
        } elseif ($this->filterAttendance === 'suggestions') {
            $query->whereNotNull('suggestions')->where('suggestions', '!=', '');
        }

        return $query->latest();
    }

    public function with(): array
    {
        $currentYear = (int) now()->year;
        $years = BirthdayAttendee::query()
            ->select('event_year')
            ->distinct()
            ->orderByDesc('event_year')
            ->pluck('event_year')
            ->filter()
            ->values()
            ->all();

        if (!in_array($currentYear, $years, true)) {
            array_unshift($years, $currentYear);
            rsort($years);
        }

        $statsQuery = BirthdayAttendee::query();
        if ($this->filterYear !== '') {
            $statsQuery->where('event_year', (int) $this->filterYear);
        }

        return [
            'availableYears' => $years,
            'attendees' => $this->buildQuery()->paginate(15),
            'totalCount' => (clone $statsQuery)->count(),
            'marinesCount' => (clone $statsQuery)->where('attendee_type', 'marine')->count(),
            'otherBranchesCount' => (clone $statsQuery)->where('attendee_type', 'other_branch')->count(),
            'familyHonorCount' => (clone $statsQuery)->where('attending_in_honor', true)->count(),
            'donorsCount' => (clone $statsQuery)->where('is_donor', true)->count(),
            'raffleNeverWonCount' => (clone $statsQuery)->where('participates_in_raffles', true)->where('has_won_raffle', false)->count(),
            'raffleWinnersCount' => (clone $statsQuery)->where('has_won_raffle', true)->count(),
            'selectedAttendee' => $this->selectedAttendeeId ? BirthdayAttendee::find($this->selectedAttendeeId) : null,
        ];
    }

    public function openAttendeeModal(int $id): void
    {
        $attendee = BirthdayAttendee::findOrFail($id);
        $this->selectedAttendeeId = $attendee->id;
        $this->editHasWonRaffle = (bool) $attendee->has_won_raffle;
        $this->editRafflePrizeWon = (string) ($attendee->raffle_prize_won ?? '');
    }

    public function closeAttendeeModal(): void
    {
        $this->selectedAttendeeId = null;
        $this->editHasWonRaffle = false;
        $this->editRafflePrizeWon = '';
    }

    public function saveRaffleStatus(): void
    {
        if (!$this->selectedAttendeeId) {
            return;
        }

        $attendee = BirthdayAttendee::findOrFail($this->selectedAttendeeId);
        $attendee->update([
            'has_won_raffle' => $this->editHasWonRaffle,
            'raffle_prize_won' => $this->editHasWonRaffle ? (trim($this->editRafflePrizeWon) ?: null) : null,
        ]);

        Flux::toast(
            text: __("Updated raffle status for {$attendee->full_name}."),
            heading: __('Saved'),
            variant: 'success',
        );

        $this->closeAttendeeModal();
    }

    public function openDeleteModal(int $id): void
    {
        $attendee = BirthdayAttendee::findOrFail($id);
        $this->deletingId = $attendee->id;
        $this->deletingName = $attendee->full_name;
    }

    public function closeDeleteModal(): void
    {
        $this->deletingId = null;
        $this->deletingName = '';
    }

    public function deleteAttendee(): void
    {
        if (!$this->deletingId) {
            return;
        }

        $attendee = BirthdayAttendee::findOrFail($this->deletingId);
        $name = $attendee->full_name;
        $attendee->delete();

        $this->closeDeleteModal();

        Flux::toast(
            text: __("Removed Birthday survey record for {$name}."),
            heading: __('Deleted'),
            variant: 'success',
        );
    }

    public function exportCsv(): StreamedResponse
    {
        $records = $this->buildQuery()->get();
        $yearSuffix = $this->filterYear !== '' ? "-{$this->filterYear}" : '-all-years';
        $filename = "marines-birthday-surveys{$yearSuffix}-" . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Event Year',
                'Date Filled Out',
                'Time Filled Out',
                'First Name',
                'Middle Name',
                'Last Name',
                'Phone',
                'Email',
                'Address',
                'City',
                'State',
                'ZIP',
                'Future Event Updates',
                'Category',
                'Service Status',
                'Branch',
                'Rank',
                'In Honor Of (Marine)',
                'Relationship',
                'Attended Before',
                'Years Attended',
                'Donor/Supporter',
                'Donation Details',
                'Participates in Raffles',
                'Has Won Raffle',
                'Raffle Prize Won',
                'Suggestions',
            ]);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->event_year ?? $r->created_at?->format('Y'),
                    $r->created_at?->format('Y-m-d'),
                    $r->created_at?->format('g:i:s A'),
                    $r->first_name,
                    $r->middle_name,
                    $r->last_name,
                    $r->phone,
                    $r->email,
                    $r->address,
                    $r->city,
                    $r->state,
                    $r->zip,
                    $r->contact_preference_label,
                    $r->attendee_type_label,
                    $r->service_status_label,
                    $r->branch,
                    $r->rank,
                    $r->honoring_marine_name,
                    $r->honoring_relationship,
                    $r->attended_before ? 'Yes' : 'No',
                    $r->years_attended ?? 0,
                    $r->is_donor ? 'Yes' : 'No',
                    $r->donation_details,
                    $r->participates_in_raffles ? 'Yes' : 'No',
                    $r->has_won_raffle ? 'Yes' : 'No',
                    $r->raffle_prize_won,
                    $r->suggestions,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}; ?>

<div class="py-6 sm:py-8 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Birthday Surveys & Raffle Tracker') }}</flux:heading>
            <flux:subheading class="text-zinc-500 dark:text-zinc-400">
                {{ __('Manage annual survey visibility, filter by event year, and track attendees, ranks, raffle winners, donors, and suggestions') }}
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if ($surveyEnabled)
                <flux:button variant="ghost" :href="route('birthday.index')" icon="arrow-top-right-on-square" wire:navigate class="border border-zinc-300 dark:border-zinc-700 text-sm">
                    {{ __('View Public Survey') }}
                </flux:button>
            @endif
            <button
                type="button"
                wire:click="exportCsv"
                class="inline-flex items-center gap-2 rounded-xl bg-red-700 hover:bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition cursor-pointer"
            >
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <span>{{ __('Export CSV') }}</span>
            </button>
        </div>
    </div>

    {{-- Survey ON / OFF Control Banner --}}
    <div class="rounded-2xl border {{ $surveyEnabled ? 'border-emerald-500/40 bg-emerald-500/5' : 'border-zinc-300 dark:border-zinc-800 bg-white dark:bg-zinc-900/60' }} p-5 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $surveyEnabled ? 'bg-emerald-500/20 text-emerald-500' : 'bg-zinc-200 dark:bg-zinc-800 text-zinc-500' }}">
                @if ($surveyEnabled)
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @else
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-white">
                        Public Birthday Survey Status:
                    </h2>
                    @if ($surveyEnabled)
                        <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-bold text-emerald-500 border border-emerald-500/30">
                            ON (Open &amp; Visible)
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-red-500/20 px-2.5 py-0.5 text-xs font-bold text-red-500 border border-red-500/30">
                            OFF (Hidden &amp; Closed)
                        </span>
                    @endif
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                    @if ($surveyEnabled)
                        The Birthday Survey link is currently visible in the website navigation bar and attendees can submit responses for {{ now()->year }}.
                    @else
                        The Birthday Survey is completely hidden from the website navigation bar and nobody can access `/birthday` until you turn it back on.
                    @endif
                </p>
            </div>
        </div>

        <button
            type="button"
            wire:click="toggleSurveyStatus"
            class="shrink-0 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-bold shadow-sm transition cursor-pointer {{ $surveyEnabled ? 'bg-zinc-800 hover:bg-zinc-700 text-white border border-zinc-700' : 'bg-emerald-600 hover:bg-emerald-500 text-white' }}"
        >
            @if ($surveyEnabled)
                <span>Turn Survey OFF</span>
            @else
                <span>Turn Survey ON</span>
            @endif
        </button>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 shadow-sm">
            <span class="text-[11px] uppercase font-bold tracking-wider text-zinc-500 dark:text-zinc-400 block">
                {{ $filterYear ? "{$filterYear} Surveys" : 'Total Surveys' }}
            </span>
            <p class="text-2xl font-black text-zinc-900 dark:text-white mt-1">{{ $totalCount }}</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 shadow-sm">
            <span class="text-[11px] uppercase font-bold tracking-wider text-red-600 dark:text-red-400 block">U.S. Marines</span>
            <p class="text-2xl font-black text-red-600 dark:text-red-400 mt-1">{{ $marinesCount }}</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 shadow-sm">
            <span class="text-[11px] uppercase font-bold tracking-wider text-amber-600 dark:text-amber-400 block">Other Branches</span>
            <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $otherBranchesCount }}</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 shadow-sm">
            <span class="text-[11px] uppercase font-bold tracking-wider text-purple-600 dark:text-purple-400 block">In Honor of Family</span>
            <p class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1">{{ $familyHonorCount }}</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 shadow-sm">
            <span class="text-[11px] uppercase font-bold tracking-wider text-emerald-600 dark:text-emerald-400 block">Raffle: Never Won</span>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $raffleNeverWonCount }}</p>
        </div>

        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 shadow-sm">
            <span class="text-[11px] uppercase font-bold tracking-wider text-blue-600 dark:text-blue-400 block">Past Raffle Winners</span>
            <p class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1">{{ $raffleWinnersCount }}</p>
        </div>
    </div>

    {{-- Search & Filters Bar --}}
    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 sm:p-5 shadow-md space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-start">
            <div>
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search name, phone, rank, prize..."
                    icon="magnifying-glass"
                />
            </div>

            <div>
                <select
                    wire:model.live="filterYear"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-3 py-2 text-sm font-semibold text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-hidden"
                >
                    <option value="">All Event Years</option>
                    @foreach ($availableYears as $yr)
                        <option value="{{ $yr }}">{{ $yr }} Birthday Survey</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select
                    wire:model.live="filterType"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-hidden"
                >
                    <option value="">All Attendee Categories</option>
                    <option value="marine">U.S. Marines Only</option>
                    <option value="other_branch">Other Military Branches</option>
                    <option value="honor">Attending in Honor of a Marine</option>
                    <option value="donor">Event Donors &amp; Supporters</option>
                </select>
            </div>

            <div>
                <select
                    wire:model.live="filterRaffle"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-hidden"
                >
                    <option value="">All Raffle Statuses</option>
                    <option value="never_won">Participates &amp; Has NEVER Won</option>
                    <option value="won">Has Won a Raffle Before</option>
                    <option value="participates">All Raffle Participants</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select
                    wire:model.live="filterAttendance"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-hidden"
                >
                    <option value="">All Attendance &amp; Feedback</option>
                    <option value="first_time">First-Time Attendees</option>
                    <option value="returning">Returning Attendees</option>
                    <option value="suggestions">Has Suggestions / Feedback</option>
                </select>

                @if ($search !== '' || $filterYear !== '' || $filterType !== '' || $filterRaffle !== '' || $filterAttendance !== '')
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="shrink-0 rounded-xl border border-zinc-300 dark:border-zinc-700 px-3 py-2 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                    >
                        Reset
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Attendees Table --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/80 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    <tr>
                        <th class="py-3.5 px-5">Year / Date &amp; Time</th>
                        <th class="py-3.5 px-5">Attendee &amp; Contact</th>
                        <th class="py-3.5 px-5">Service / Connection</th>
                        <th class="py-3.5 px-5">Attendance</th>
                        <th class="py-3.5 px-5">Raffles &amp; Prizes</th>
                        <th class="py-3.5 px-5">Suggestions</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800/80 text-zinc-700 dark:text-zinc-300">
                    @forelse ($attendees as $person)
                        <tr wire:key="attendee-row-{{ $person->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition align-top">
                            {{-- Year, Date & Time Filled Out --}}
                            <td class="py-4 px-5 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md bg-amber-500/15 px-2 py-0.5 text-xs font-extrabold text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    {{ $person->event_year ?? $person->created_at?->format('Y') }} Survey
                                </span>
                                <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 mt-1">
                                    {{ $person->created_at?->format('M j, Y') }}
                                </div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                    {{ $person->created_at?->format('g:i A') }}
                                </div>
                            </td>

                            {{-- Name, Phone, Address --}}
                            <td class="py-4 px-5">
                                <div class="font-bold text-zinc-900 dark:text-white">
                                    {{ $person->full_name }}
                                </div>
                                <div class="text-xs text-amber-600 dark:text-amber-400 font-medium mt-0.5">
                                    {{ $person->phone ?: 'No phone' }}
                                    @if ($person->email)
                                        &bull; <span class="text-zinc-500">{{ $person->email }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                    {{ $person->full_address }}
                                </div>
                                <div class="mt-1">
                                    @if ($person->notify_future_events)
                                        <span class="inline-flex items-center rounded bg-emerald-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            Alerts: {{ $person->contact_preference_label }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded bg-zinc-500/10 px-1.5 py-0.5 text-[10px] font-medium text-zinc-500">
                                            Alerts: Opted Out
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Service / Branch / Rank / Family Honor --}}
                            <td class="py-4 px-5 space-y-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if ($person->attendee_type === 'marine')
                                        <span class="inline-flex items-center rounded-md bg-red-600/15 px-2 py-0.5 text-xs font-bold text-red-600 dark:text-red-400 border border-red-500/20">
                                            USMC ({{ $person->service_status === 'active' ? 'Active' : 'Veteran' }})
                                        </span>
                                    @elseif ($person->attendee_type === 'other_branch')
                                        <span class="inline-flex items-center rounded-md bg-amber-500/15 px-2 py-0.5 text-xs font-bold text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            {{ $person->branch ?: 'Other Branch' }} ({{ $person->service_status === 'active' ? 'Active' : 'Veteran' }})
                                        </span>
                                    @elseif ($person->attendee_type === 'family_honor')
                                        <span class="inline-flex items-center rounded-md bg-purple-500/15 px-2 py-0.5 text-xs font-bold text-purple-600 dark:text-purple-400 border border-purple-500/20">
                                            Family / In Honor
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-emerald-500/15 px-2 py-0.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            Supporter / Donor
                                        </span>
                                    @endif

                                    @if ($person->rank)
                                        <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                            Rank: {{ $person->rank }}
                                        </span>
                                    @endif
                                </div>

                                @if ($person->attending_in_honor && ($person->honoring_marine_name || $person->honoring_relationship))
                                    <div class="text-xs text-purple-600 dark:text-purple-300">
                                        In Honor of: <span class="font-semibold">{{ $person->honoring_marine_name ?: 'Marine Relative' }}</span>
                                        @if ($person->honoring_relationship)
                                            ({{ $person->honoring_relationship }})
                                        @endif
                                    </div>
                                @endif

                                @if ($person->is_donor)
                                    <div class="text-xs text-emerald-600 dark:text-emerald-400">
                                        <span class="font-bold">Donor:</span> {{ Str::limit($person->donation_details ?: 'Event Supporter', 50) }}
                                    </div>
                                @endif
                            </td>

                            {{-- Attendance History --}}
                            <td class="py-4 px-5">
                                @if ($person->attended_before)
                                    <span class="inline-flex items-center rounded-md bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                        {{ $person->years_attended }} {{ Str::plural('year', $person->years_attended ?? 1) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-amber-500/15 px-2 py-0.5 text-xs font-bold text-amber-600 dark:text-amber-400">
                                        1st Birthday!
                                    </span>
                                @endif
                            </td>

                            {{-- Raffle Tracking --}}
                            <td class="py-4 px-5">
                                @if (!$person->participates_in_raffles && !$person->has_won_raffle)
                                    <span class="text-xs text-zinc-400">Not participating</span>
                                @elseif ($person->has_won_raffle)
                                    <div>
                                        <span class="inline-flex items-center gap-1 rounded-md bg-blue-500/15 px-2 py-0.5 text-xs font-bold text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                            Won Before
                                        </span>
                                        @if ($person->raffle_prize_won)
                                            <div class="text-xs text-zinc-600 dark:text-zinc-300 mt-1 font-medium">
                                                Prize: {{ $person->raffle_prize_won }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-500/15 px-2 py-0.5 text-xs font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        Participates &bull; Never Won
                                    </span>
                                @endif
                            </td>

                            {{-- Suggestions --}}
                            <td class="py-4 px-5 max-w-xs">
                                @if ($person->suggestions)
                                    <p class="text-xs text-zinc-700 dark:text-zinc-300 line-clamp-2" title="{{ $person->suggestions }}">
                                        "{{ $person->suggestions }}"
                                    </p>
                                @else
                                    <span class="text-xs text-zinc-400">&mdash;</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="openAttendeeModal({{ $person->id }})"
                                        class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 px-2.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200 transition cursor-pointer"
                                    >
                                        View / Raffle
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="openDeleteModal({{ $person->id }})"
                                        class="inline-flex items-center rounded-lg bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-950/70 p-1.5 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/50 transition cursor-pointer"
                                        title="Delete Record"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-6 text-center text-zinc-500">
                                No Birthday survey records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
            {{ $attendees->links() }}
        </div>
    </div>

    {{-- View Details & Update Raffle Winner Modal --}}
    @if ($selectedAttendee)
        <div wire:key="attendee-modal-{{ $selectedAttendee->id }}" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/75 backdrop-blur-xs" wire:click="closeAttendeeModal"></div>

                <div class="relative w-full max-w-xl rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-2xl z-10 text-zinc-900 dark:text-zinc-100 space-y-5">
                    <div class="flex items-start justify-between border-b border-zinc-200 dark:border-zinc-800 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xl font-bold text-zinc-900 dark:text-white">{{ $selectedAttendee->full_name }}</h3>
                                <span class="inline-flex items-center rounded-md bg-amber-500/15 px-2 py-0.5 text-xs font-bold text-amber-500 border border-amber-500/20">
                                    {{ $selectedAttendee->event_year ?? $selectedAttendee->created_at?->format('Y') }} Survey
                                </span>
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                Filled out on {{ $selectedAttendee->created_at?->format('F j, Y \a\t g:i A') }} &bull; {{ $selectedAttendee->attendee_type_label }}
                            </p>
                        </div>
                        <button type="button" wire:click="closeAttendeeModal" class="rounded-lg p-1.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-xs uppercase font-bold text-zinc-400 block">Phone &amp; Email</span>
                            <p class="font-medium">{{ $selectedAttendee->phone ?: 'Not provided' }}</p>
                            @if ($selectedAttendee->email)
                                <p class="text-xs text-zinc-500">{{ $selectedAttendee->email }}</p>
                            @endif
                            <p class="text-xs text-emerald-500 mt-0.5">Future Alerts: {{ $selectedAttendee->contact_preference_label }}</p>
                        </div>

                        <div>
                            <span class="text-xs uppercase font-bold text-zinc-400 block">Address</span>
                            <p class="font-medium">{{ $selectedAttendee->full_address }}</p>
                        </div>

                        <div>
                            <span class="text-xs uppercase font-bold text-zinc-400 block">Military Service &amp; Rank</span>
                            <p class="font-medium">
                                {{ $selectedAttendee->service_status_label }}
                                @if ($selectedAttendee->branch)
                                    ({{ $selectedAttendee->branch }})
                                @endif
                            </p>
                            @if ($selectedAttendee->rank)
                                <p class="text-xs text-amber-500 font-semibold">Rank: {{ $selectedAttendee->rank }}</p>
                            @endif
                        </div>

                        <div>
                            <span class="text-xs uppercase font-bold text-zinc-400 block">Birthday Attendance</span>
                            <p class="font-medium">
                                @if ($selectedAttendee->attended_before)
                                    Attended {{ $selectedAttendee->years_attended }} {{ Str::plural('year', $selectedAttendee->years_attended ?? 1) }}
                                @else
                                    First-Time Attendee
                                @endif
                            </p>
                        </div>
                    </div>

                    @if ($selectedAttendee->attending_in_honor)
                        <div class="rounded-xl bg-purple-500/10 border border-purple-500/20 p-3.5 text-sm">
                            <span class="text-xs uppercase font-bold text-purple-500 block">Attending in Honor of a Marine</span>
                            <p class="font-semibold mt-0.5">
                                {{ $selectedAttendee->honoring_marine_name ?: 'Marine Family Member' }}
                                @if ($selectedAttendee->honoring_relationship)
                                    ({{ $selectedAttendee->honoring_relationship }})
                                @endif
                            </p>
                        </div>
                    @endif

                    @if ($selectedAttendee->is_donor)
                        <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-3.5 text-sm">
                            <span class="text-xs uppercase font-bold text-emerald-500 block">Event Donor / Supporter</span>
                            <p class="mt-0.5">{{ $selectedAttendee->donation_details ?: 'Donates / supports the event' }}</p>
                        </div>
                    @endif

                    @if ($selectedAttendee->suggestions)
                        <div class="rounded-xl bg-zinc-100 dark:bg-zinc-800/80 p-3.5 text-sm">
                            <span class="text-xs uppercase font-bold text-zinc-500 dark:text-zinc-400 block">Suggestions &amp; Feedback</span>
                            <p class="mt-1 whitespace-pre-line">{{ $selectedAttendee->suggestions }}</p>
                        </div>
                    @endif

                    {{-- Quick Raffle Winner Update Form --}}
                    <form wire:submit="saveRaffleStatus" class="border-t border-zinc-200 dark:border-zinc-800 pt-4 space-y-4">
                        <h4 class="text-sm font-bold text-amber-600 dark:text-amber-400">Update Raffle Winner Record</h4>

                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" wire:model.live="editHasWonRaffle" class="size-4 rounded border-zinc-300 text-red-600 focus:ring-red-500" />
                            <span class="text-sm font-medium">Mark as having won a raffle prize</span>
                        </label>

                        @if ($editHasWonRaffle)
                            <div>
                                <flux:input
                                    wire:model="editRafflePrizeWon"
                                    :label="__('Raffle Prize Won (e.g. Pit Boss Smoker, Blackstone Griddle)')"
                                    placeholder="Enter prize won and year..."
                                />
                            </div>
                        @endif

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" wire:click="closeAttendeeModal" class="px-4 py-2 text-sm font-medium rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
                                Close
                            </button>
                            <button type="submit" class="px-5 py-2 text-sm font-semibold rounded-xl bg-red-700 hover:bg-red-600 text-white shadow-md cursor-pointer">
                                Save Raffle Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($deletingId)
        <div wire:key="delete-attendee-modal-{{ $deletingId }}" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/75 backdrop-blur-xs" wire:click="closeDeleteModal"></div>
                <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 shadow-2xl z-10">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Delete Survey Record</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300 mt-2">
                        Are you sure you want to delete the Birthday survey record for <span class="font-bold">{{ $deletingName }}</span>?
                    </p>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="closeDeleteModal" class="px-4 py-2 text-sm font-medium rounded-xl text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" wire:click="deleteAttendee" class="px-5 py-2 text-sm font-semibold rounded-xl bg-red-700 hover:bg-red-600 text-white cursor-pointer">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
