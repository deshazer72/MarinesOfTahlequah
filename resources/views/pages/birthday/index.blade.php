<?php

use App\Models\BirthdayAttendee;
use App\Models\SiteSetting;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Birthday Survey - Marines of Tahlequah')]
#[Layout('layouts.public', ['title' => 'Birthday Survey - Marines of Tahlequah'])]
class extends Component {
    // Wizard Step (1 to 4)
    public int $currentStep = 1;

    // Step 1: Personal & Contact Information
    public string $first_name = '';
    public string $middle_name = '';
    public string $last_name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $city = 'Tahlequah';
    public string $state = 'OK';
    public string $zip = '';

    // Future Event Contact Preferences
    public string $notify_future_events = 'yes';
    // 'email', 'phone', 'both'
    public string $preferred_contact_method = 'email';

    // Step 2: Military & Connection Info
    // 'marine', 'other_branch', 'family_honor', 'supporter'
    public string $attendee_type = 'marine';
    // 'active', 'veteran'
    public string $service_status = 'veteran';
    public string $branch = 'USMC';
    public string $rank = '';

    // Family / In Honor of a Marine
    public bool $attending_in_honor = false;
    public string $honoring_relationship = '';
    public string $honoring_marine_name = '';

    // Event Donor / Supporter
    public bool $is_donor = false;
    public string $donation_details = '';

    // Step 3: Attendance History & Raffles
    public string $attended_before = 'no';
    public ?int $years_attended = null;
    public string $participates_in_raffles = 'yes';
    public string $has_won_raffle = 'no';
    public string $raffle_prize_won = '';

    // Step 4: Suggestions
    public string $suggestions = '';

    // Submission state
    public bool $submitted = false;
    public string $submittedName = '';

    public function mount(): void
    {
        if (!SiteSetting::isBirthdaySurveyEnabled()) {
            abort(404);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $parts = preg_split('/\s+/', trim($user->name ?? ''));
            if (count($parts) === 1) {
                $this->first_name = $parts[0];
            } elseif (count($parts) === 2) {
                $this->first_name = $parts[0];
                $this->last_name = $parts[1];
            } elseif (count($parts) >= 3) {
                $this->first_name = array_shift($parts);
                $this->last_name = array_pop($parts);
                $this->middle_name = implode(' ', $parts);
            }
            $this->email = $user->email ?? '';
        }
    }

    public function updatedAttendeeType(string $value): void
    {
        if ($value === 'marine') {
            $this->branch = 'USMC';
            if (!in_array($this->service_status, ['active', 'veteran'], true)) {
                $this->service_status = 'veteran';
            }
        } elseif ($value === 'other_branch') {
            if ($this->branch === 'USMC' || $this->branch === '') {
                $this->branch = 'Army';
            }
            if (!in_array($this->service_status, ['active', 'veteran'], true)) {
                $this->service_status = 'veteran';
            }
        } elseif ($value === 'family_honor') {
            $this->service_status = 'civilian';
            $this->branch = '';
            $this->rank = '';
            $this->attending_in_honor = true;
        } elseif ($value === 'supporter') {
            $this->service_status = 'civilian';
            $this->branch = '';
            $this->rank = '';
            $this->is_donor = true;
        }
    }

    protected function stepOneRules(): array
    {
        $wantsUpdates = $this->notify_future_events === 'yes';
        $requiresEmail = $wantsUpdates && in_array($this->preferred_contact_method, ['email', 'both'], true);
        $requiresPhone = $wantsUpdates && in_array($this->preferred_contact_method, ['phone', 'both'], true);

        return [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'notify_future_events' => 'required|in:yes,no',
            'preferred_contact_method' => $wantsUpdates ? 'required|in:email,phone,both' : 'nullable|string|max:30',
            'phone' => ($requiresPhone ? 'required' : 'nullable') . '|string|max:40',
            'email' => ($requiresEmail ? 'required' : 'nullable') . '|email|max:255',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:20',
        ];
    }

    protected function stepTwoRules(): array
    {
        $isMilitary = in_array($this->attendee_type, ['marine', 'other_branch'], true);

        return [
            'attendee_type' => 'required|in:marine,other_branch,family_honor,supporter',
            'service_status' => $isMilitary ? 'required|in:active,veteran' : 'nullable|string|max:50',
            'branch' => $this->attendee_type === 'other_branch' ? 'required|string|max:100' : 'nullable|string|max:100',
            'rank' => $isMilitary ? 'required|string|max:100' : 'nullable|string|max:100',
            'attending_in_honor' => 'boolean',
            'honoring_relationship' => 'nullable|string|max:100',
            'honoring_marine_name' => 'nullable|string|max:255',
            'is_donor' => 'boolean',
            'donation_details' => 'nullable|string|max:1000',
        ];
    }

    protected function stepThreeRules(): array
    {
        return [
            'attended_before' => 'required|in:yes,no',
            'years_attended' => $this->attended_before === 'yes' ? 'required|integer|min:1|max:100' : 'nullable|integer|min:0|max:100',
            'participates_in_raffles' => 'required|in:yes,no',
            'has_won_raffle' => 'required|in:yes,no',
            'raffle_prize_won' => 'nullable|string|max:255',
        ];
    }

    protected function stepFourRules(): array
    {
        return [
            'suggestions' => 'nullable|string|max:5000',
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'phone.required' => 'Please enter your phone number so we can contact you about future events.',
            'email.required' => 'Please enter your email address so we can send you future event updates.',
            'rank.required' => $this->service_status === 'active'
                ? 'Please enter your current rank.'
                : 'Please enter your last rank held.',
            'years_attended.required' => 'Please enter how many years you have attended the Birthday celebration.',
        ];
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate($this->stepOneRules(), $this->validationMessages());
            $this->currentStep = 2;
        } elseif ($this->currentStep === 2) {
            $this->validate($this->stepTwoRules(), $this->validationMessages());
            $this->currentStep = 3;
        } elseif ($this->currentStep === 3) {
            $this->validate($this->stepThreeRules(), $this->validationMessages());
            $this->currentStep = 4;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 4) {
            return;
        }

        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    public function submit(): void
    {
        if (!SiteSetting::isBirthdaySurveyEnabled()) {
            abort(404);
        }

        $allRules = array_merge(
            $this->stepOneRules(),
            $this->stepTwoRules(),
            $this->stepThreeRules(),
            $this->stepFourRules()
        );

        $this->validate($allRules, $this->validationMessages());

        $isMilitary = in_array($this->attendee_type, ['marine', 'other_branch'], true);
        $wantsUpdates = $this->notify_future_events === 'yes';
        $attendedBeforeBool = $this->attended_before === 'yes';
        $participatesBool = $this->participates_in_raffles === 'yes';
        $hasWonBool = $this->has_won_raffle === 'yes';

        $record = BirthdayAttendee::create([
            'first_name' => trim($this->first_name),
            'middle_name' => trim($this->middle_name) ?: null,
            'last_name' => trim($this->last_name),
            'phone' => trim($this->phone) ?: null,
            'email' => trim($this->email) ?: null,
            'address' => trim($this->address),
            'city' => trim($this->city) ?: null,
            'state' => trim($this->state) ?: null,
            'zip' => trim($this->zip) ?: null,
            'notify_future_events' => $wantsUpdates,
            'preferred_contact_method' => $wantsUpdates ? $this->preferred_contact_method : null,
            'attendee_type' => $this->attendee_type,
            'service_status' => $isMilitary ? $this->service_status : 'civilian',
            'branch' => $this->attendee_type === 'marine'
                ? 'USMC'
                : ($this->attendee_type === 'other_branch' ? trim($this->branch) : null),
            'rank' => $isMilitary ? trim($this->rank) : null,
            'attending_in_honor' => $this->attendee_type === 'family_honor' || $this->attending_in_honor,
            'honoring_relationship' => ($this->attendee_type === 'family_honor' || $this->attending_in_honor)
                ? (trim($this->honoring_relationship) ?: null)
                : null,
            'honoring_marine_name' => ($this->attendee_type === 'family_honor' || $this->attending_in_honor)
                ? (trim($this->honoring_marine_name) ?: null)
                : null,
            'is_donor' => $this->attendee_type === 'supporter' || $this->is_donor,
            'donation_details' => ($this->attendee_type === 'supporter' || $this->is_donor)
                ? (trim($this->donation_details) ?: null)
                : null,
            'attended_before' => $attendedBeforeBool,
            'years_attended' => $attendedBeforeBool ? $this->years_attended : 0,
            'participates_in_raffles' => $participatesBool,
            'has_won_raffle' => $hasWonBool,
            'raffle_prize_won' => $hasWonBool ? (trim($this->raffle_prize_won) ?: null) : null,
            'suggestions' => trim($this->suggestions) ?: null,
            'event_year' => (int) now()->year,
            'user_id' => Auth::id(),
        ]);

        $this->submittedName = $record->full_name;
        $this->submitted = true;

        Flux::toast(
            text: __('Thank you for completing the Marines of Tahlequah Birthday Survey!'),
            heading: __('Semper Fidelis'),
            variant: 'success',
        );
    }

    public function resetForm(): void
    {
        $this->reset([
            'currentStep',
            'first_name',
            'middle_name',
            'last_name',
            'phone',
            'email',
            'address',
            'zip',
            'notify_future_events',
            'preferred_contact_method',
            'attendee_type',
            'service_status',
            'branch',
            'rank',
            'attending_in_honor',
            'honoring_relationship',
            'honoring_marine_name',
            'is_donor',
            'donation_details',
            'attended_before',
            'years_attended',
            'participates_in_raffles',
            'has_won_raffle',
            'raffle_prize_won',
            'suggestions',
            'submitted',
            'submittedName',
        ]);
        $this->currentStep = 1;
        $this->city = 'Tahlequah';
        $this->state = 'OK';
        $this->notify_future_events = 'yes';
        $this->preferred_contact_method = 'email';
        $this->attendee_type = 'marine';
        $this->service_status = 'veteran';
        $this->branch = 'USMC';
        $this->attended_before = 'no';
        $this->participates_in_raffles = 'yes';
        $this->has_won_raffle = 'no';
    }
}; ?>

<div class="py-12 md:py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-semibold text-amber-400 mb-3">
                <span>Annual Marine Corps Birthday Celebration &bull; Tahlequah, OK</span>
            </div>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-zinc-900 dark:text-white tracking-tight">
                Birthday Survey
            </h1>
            <p class="mt-3 text-base text-zinc-600 dark:text-zinc-300 max-w-2xl mx-auto">
                Welcome to our biggest event of the year! Please complete the short 4-step survey below.
            </p>

            @auth
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <div class="mt-4 flex justify-center">
                        <flux:button variant="ghost" size="sm" :href="route('admin.birthday')" icon="clipboard-document-list" wire:navigate class="text-xs text-amber-600 dark:text-amber-400 bg-amber-500/10 border border-amber-500/30">
                            {{ __('View All Survey Responses & Raffle Tracker (Admin)') }}
                        </flux:button>
                    </div>
                @endif
            @endauth
        </div>

        @if ($submitted)
            {{-- Confirmation Card --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-8 sm:p-10 shadow-xl text-center space-y-6">
                <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                    <svg class="size-9" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div class="space-y-2">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-white">
                        Thank You, {{ $submittedName }}!
                    </h2>
                    <p class="text-zinc-600 dark:text-zinc-300 max-w-xl mx-auto">
                        Your Birthday Survey, contact preferences, raffle details, and feedback have been saved. We are honored to have you celebrate with the Marines of Tahlequah!
                    </p>
                </div>

                <div class="pt-4 flex flex-wrap items-center justify-center gap-4">
                    <flux:button variant="primary" wire:click="resetForm" icon="plus" class="bg-red-700 hover:bg-red-600 text-white font-semibold">
                        Submit Another Survey
                    </flux:button>

                    <flux:button variant="ghost" :href="route('home')" wire:navigate class="border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-200">
                        Return to Home
                    </flux:button>
                </div>
            </div>
        @else
            {{-- Stepper Progress Indicator --}}
            @php
                $steps = [
                    1 => 'Contact Info',
                    2 => 'Service & Honor',
                    3 => 'Attendance & Raffles',
                    4 => 'Suggestions',
                ];
            @endphp
            <div class="mb-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-4 sm:p-5 shadow-md">
                <div class="flex items-center justify-between text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-3">
                    <span>Step {{ $currentStep }} of 4: <strong class="text-zinc-900 dark:text-white">{{ $steps[$currentStep] }}</strong></span>
                    <span>{{ $currentStep * 25 }}% Complete</span>
                </div>

                <div class="grid grid-cols-4 gap-2">
                    @foreach ($steps as $stepNum => $stepLabel)
                        <button
                            type="button"
                            wire:click="goToStep({{ $stepNum }})"
                            @disabled($stepNum >= $currentStep)
                            class="text-left group focus:outline-hidden {{ $stepNum < $currentStep ? 'cursor-pointer' : 'cursor-default' }}"
                        >
                            <div class="h-2 w-full rounded-full transition-colors {{ $stepNum < $currentStep ? 'bg-emerald-500' : ($stepNum === $currentStep ? 'bg-red-600' : 'bg-zinc-200 dark:bg-zinc-800') }}"></div>
                            <div class="mt-2 flex items-center gap-1.5">
                                <span class="flex size-5 shrink-0 items-center justify-center rounded-full text-[11px] font-bold {{ $stepNum < $currentStep ? 'bg-emerald-500/20 text-emerald-500' : ($stepNum === $currentStep ? 'bg-red-700 text-amber-300' : 'bg-zinc-200 dark:bg-zinc-800 text-zinc-500') }}">
                                    @if ($stepNum < $currentStep)
                                        &#10003;
                                    @else
                                        {{ $stepNum }}
                                    @endif
                                </span>
                                <span class="hidden sm:inline text-xs font-medium truncate {{ $stepNum === $currentStep ? 'text-zinc-900 dark:text-white font-bold' : 'text-zinc-500 dark:text-zinc-400' }}">
                                    {{ $stepLabel }}
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <form wire:submit="submit">
                <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/60 p-6 sm:p-8 shadow-xl space-y-6">
                    {{-- STEP 1: Name, Contact Info & Future Event Notifications --}}
                    @if ($currentStep === 1)
                        <div class="flex items-center gap-3 border-b border-zinc-200 dark:border-zinc-800 pb-4">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-red-700 text-amber-300 font-black text-sm shadow-xs">
                                1
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Personal & Contact Information</h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Your name, mailing address, and whether you'd like future event notifications</p>
                            </div>
                        </div>

                        {{-- Name Row: First, Middle (Optional), Last --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                            <div>
                                <flux:input
                                    wire:model="first_name"
                                    :label="__('First Name *')"
                                    required
                                    placeholder="First name"
                                />
                            </div>
                            <div>
                                <flux:input
                                    wire:model="middle_name"
                                    :label="__('Middle Name (Optional)')"
                                    placeholder="Middle name or initial"
                                />
                            </div>
                            <div>
                                <flux:input
                                    wire:model="last_name"
                                    :label="__('Last Name *')"
                                    required
                                    placeholder="Last name"
                                />
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6 space-y-4">
                            <div>
                                <flux:input
                                    wire:model="address"
                                    :label="__('Mailing / Street Address *')"
                                    required
                                    placeholder="123 Main St or PO Box"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                                <div>
                                    <flux:input
                                        wire:model="city"
                                        :label="__('City')"
                                        placeholder="Tahlequah"
                                    />
                                </div>
                                <div>
                                    <flux:input
                                        wire:model="state"
                                        :label="__('State')"
                                        placeholder="OK"
                                    />
                                </div>
                                <div>
                                    <flux:input
                                        wire:model="zip"
                                        :label="__('ZIP Code')"
                                        placeholder="74464"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- Future Event Notifications Preference --}}
                        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">
                                        Contact me for future Marines of Tahlequah events? *
                                    </label>
                                    <div class="inline-flex rounded-xl border border-zinc-300 dark:border-zinc-700 p-1 bg-zinc-100 dark:bg-zinc-800">
                                        <button
                                            type="button"
                                            wire:click="$set('notify_future_events', 'yes')"
                                            class="rounded-lg px-4 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $notify_future_events === 'yes' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                        >
                                            Yes, Notify Me
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="$set('notify_future_events', 'no')"
                                            class="rounded-lg px-4 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $notify_future_events === 'no' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                        >
                                            No Thanks
                                        </button>
                                    </div>
                                </div>

                                @if ($notify_future_events === 'yes')
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">
                                            How would you like to be contacted? *
                                        </label>
                                        <div class="inline-flex rounded-xl border border-zinc-300 dark:border-zinc-700 p-1 bg-zinc-100 dark:bg-zinc-800">
                                            <button
                                                type="button"
                                                wire:click="$set('preferred_contact_method', 'email')"
                                                class="rounded-lg px-4 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $preferred_contact_method === 'email' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                            >
                                                Email
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="$set('preferred_contact_method', 'phone')"
                                                class="rounded-lg px-4 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $preferred_contact_method === 'phone' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                            >
                                                Phone / Text
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="$set('preferred_contact_method', 'both')"
                                                class="rounded-lg px-4 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $preferred_contact_method === 'both' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                            >
                                                Both
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if ($notify_future_events === 'yes')
                                @php
                                    $emailRequired = in_array($preferred_contact_method, ['email', 'both'], true);
                                    $phoneRequired = in_array($preferred_contact_method, ['phone', 'both'], true);
                                @endphp
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                                    <div>
                                        <flux:input
                                            wire:model="phone"
                                            type="tel"
                                            :label="$phoneRequired ? __('Phone Number *') : __('Phone Number (Optional)')"
                                            :required="$phoneRequired"
                                            placeholder="(918) 555-0123"
                                        />
                                    </div>
                                    <div>
                                        <flux:input
                                            wire:model="email"
                                            type="email"
                                            :label="$emailRequired ? __('Email Address *') : __('Email Address (Optional)')"
                                            :required="$emailRequired"
                                            placeholder="you@example.com"
                                        />
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-end border-t border-zinc-200 dark:border-zinc-800 pt-6">
                            <flux:button type="button" variant="primary" wire:click="nextStep" icon-trailing="arrow-right" class="bg-red-700 hover:bg-red-600 text-white font-semibold px-6">
                                Next: Service & Connection
                            </flux:button>
                        </div>
                    @endif

                    {{-- STEP 2: Military Service, Family Honor & Connection --}}
                    @if ($currentStep === 2)
                        <div class="flex items-center gap-3 border-b border-zinc-200 dark:border-zinc-800 pb-4">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-red-700 text-amber-300 font-black text-sm shadow-xs">
                                2
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Military Service & Connection</h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Are you a Marine, serving/veteran of another branch, attending in honor of family, or an event supporter?</p>
                            </div>
                        </div>

                        {{-- Attendee Category Cards --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-3">
                                How are you connected to the Marines of Tahlequah Birthday? *
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {{-- Option: US Marine --}}
                                <button
                                    type="button"
                                    wire:click="$set('attendee_type', 'marine')"
                                    class="flex items-start gap-3 rounded-xl border p-4 text-left transition cursor-pointer {{ $attendee_type === 'marine' ? 'border-amber-500 bg-amber-500/10' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700' }}"
                                >
                                    <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border {{ $attendee_type === 'marine' ? 'border-amber-500 bg-amber-500 text-zinc-900' : 'border-zinc-400 dark:border-zinc-600' }}">
                                        @if ($attendee_type === 'marine')
                                            <span class="size-2 rounded-full bg-zinc-900"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-zinc-900 dark:text-white">U.S. Marine Corps</span>
                                        <span class="block text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Active Duty or Veteran Marine</span>
                                    </div>
                                </button>

                                {{-- Option: Other Military Branch --}}
                                <button
                                    type="button"
                                    wire:click="$set('attendee_type', 'other_branch')"
                                    class="flex items-start gap-3 rounded-xl border p-4 text-left transition cursor-pointer {{ $attendee_type === 'other_branch' ? 'border-amber-500 bg-amber-500/10' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700' }}"
                                >
                                    <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border {{ $attendee_type === 'other_branch' ? 'border-amber-500 bg-amber-500 text-zinc-900' : 'border-zinc-400 dark:border-zinc-600' }}">
                                        @if ($attendee_type === 'other_branch')
                                            <span class="size-2 rounded-full bg-zinc-900"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-zinc-900 dark:text-white">Other Military Branch</span>
                                        <span class="block text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Army, Navy, Air Force, Coast Guard, Space Force, or Guard</span>
                                    </div>
                                </button>

                                {{-- Option: Family Member in Honor of a Marine --}}
                                <button
                                    type="button"
                                    wire:click="$set('attendee_type', 'family_honor')"
                                    class="flex items-start gap-3 rounded-xl border p-4 text-left transition cursor-pointer {{ $attendee_type === 'family_honor' ? 'border-amber-500 bg-amber-500/10' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700' }}"
                                >
                                    <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border {{ $attendee_type === 'family_honor' ? 'border-amber-500 bg-amber-500 text-zinc-900' : 'border-zinc-400 dark:border-zinc-600' }}">
                                        @if ($attendee_type === 'family_honor')
                                            <span class="size-2 rounded-full bg-zinc-900"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-zinc-900 dark:text-white">Family Member (In Honor of a Marine)</span>
                                        <span class="block text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Attending in honor of a father, grandfather, spouse, or relative</span>
                                    </div>
                                </button>

                                {{-- Option: Event Donor / Community Supporter --}}
                                <button
                                    type="button"
                                    wire:click="$set('attendee_type', 'supporter')"
                                    class="flex items-start gap-3 rounded-xl border p-4 text-left transition cursor-pointer {{ $attendee_type === 'supporter' ? 'border-amber-500 bg-amber-500/10' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700' }}"
                                >
                                    <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border {{ $attendee_type === 'supporter' ? 'border-amber-500 bg-amber-500 text-zinc-900' : 'border-zinc-400 dark:border-zinc-600' }}">
                                        @if ($attendee_type === 'supporter')
                                            <span class="size-2 rounded-full bg-zinc-900"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold text-zinc-900 dark:text-white">Supporter / Donor / Community Guest</span>
                                        <span class="block text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Supporting our veterans, donating prizes, or celebrating with us</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        {{-- Active vs Veteran & Rank --}}
                        @if (in_array($attendee_type, ['marine', 'other_branch'], true))
                            <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6 space-y-5">
                                @if ($attendee_type === 'other_branch')
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">
                                            Which Branch of Service? *
                                        </label>
                                        <select
                                            wire:model.live="branch"
                                            class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3.5 py-2.5 text-sm font-medium text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-hidden"
                                        >
                                            <option value="Army">U.S. Army</option>
                                            <option value="Navy">U.S. Navy</option>
                                            <option value="Air Force">U.S. Air Force</option>
                                            <option value="Coast Guard">U.S. Coast Guard</option>
                                            <option value="National Guard">National Guard</option>
                                            <option value="Space Force">U.S. Space Force</option>
                                        </select>
                                        @error('branch')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                                    {{-- Active or Veteran --}}
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">
                                            Are you currently Active Duty or a Veteran? *
                                        </label>
                                        <div class="inline-flex rounded-xl border border-zinc-300 dark:border-zinc-700 p-1 bg-zinc-100 dark:bg-zinc-800">
                                            <button
                                                type="button"
                                                wire:click="$set('service_status', 'veteran')"
                                                class="rounded-lg px-5 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $service_status === 'veteran' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                            >
                                                Veteran
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="$set('service_status', 'active')"
                                                class="rounded-lg px-5 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $service_status === 'active' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                            >
                                                Active Duty
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Rank Field --}}
                                    <div>
                                        <flux:input
                                            wire:model="rank"
                                            :label="$service_status === 'active' ? __('What is your current rank? *') : __('What was your last rank held? *')"
                                            required
                                            :placeholder="$attendee_type === 'marine' ? 'e.g. Corporal (Cpl), Sergeant (Sgt), GySgt...' : 'e.g. SGT, SSG, PO1, Chief, CPT...'"
                                        />
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Additional Checkboxes for Military / Supporters who are ALSO attending in honor or donating --}}
                        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-5 space-y-5">
                            @if ($attendee_type !== 'family_honor')
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        wire:model.live="attending_in_honor"
                                        class="mt-1 size-4 rounded border-zinc-300 dark:border-zinc-700 text-red-600 focus:ring-red-500"
                                    />
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        I am also attending in honor of a Marine family member (e.g., father, grandfather, brother, spouse)
                                    </span>
                                </label>
                            @endif

                            @if ($attendee_type === 'family_honor' || $attending_in_honor)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1 items-start">
                                    <div>
                                        <flux:input
                                            wire:model="honoring_relationship"
                                            :label="__('Their Relationship to You')"
                                            placeholder="e.g. Father, Grandfather, Uncle, Brother, Spouse"
                                        />
                                    </div>
                                    <div>
                                        <flux:input
                                            wire:model="honoring_marine_name"
                                            :label="__('Marine\'s Name & Rank (if known)')"
                                            placeholder="e.g. GySgt John Doe, USMC"
                                        />
                                    </div>
                                </div>
                            @endif

                            @if ($attendee_type !== 'supporter')
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        wire:model.live="is_donor"
                                        class="mt-1 size-4 rounded border-zinc-300 dark:border-zinc-700 text-red-600 focus:ring-red-500"
                                    />
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        I donate raffle items, equipment, funds, or volunteer support for the Birthday event
                                    </span>
                                </label>
                            @endif

                            @if ($attendee_type === 'supporter' || $is_donor)
                                <div class="pt-1">
                                    <flux:textarea
                                        wire:model="donation_details"
                                        :label="__('What items or support do you donate / provide? (Optional)')"
                                        rows="2"
                                        placeholder="e.g. Pit Boss smoker, Blackstone griddle, raffle prizes, financial donation, website support..."
                                    />
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-800 pt-6">
                            <flux:button type="button" variant="ghost" wire:click="previousStep" icon="arrow-left">
                                Back
                            </flux:button>
                            <flux:button type="button" variant="primary" wire:click="nextStep" icon-trailing="arrow-right" class="bg-red-700 hover:bg-red-600 text-white font-semibold px-6">
                                Next: Attendance & Raffles
                            </flux:button>
                        </div>
                    @endif

                    {{-- STEP 3: Birthday Attendance & Raffle Tracking --}}
                    @if ($currentStep === 3)
                        <div class="flex items-center gap-3 border-b border-zinc-200 dark:border-zinc-800 pb-4">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-red-700 text-amber-300 font-black text-sm shadow-xs">
                                3
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Birthday Attendance & Party Raffles</h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Help us track repeat attendees and our annual raffle participants & winners</p>
                            </div>
                        </div>

                        {{-- Attended Before Yes/No --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                            <div>
                                <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">
                                    Have you been to the Marines of Tahlequah Birthday before? *
                                </label>
                                <div class="inline-flex rounded-xl border border-zinc-300 dark:border-zinc-700 p-1 bg-zinc-100 dark:bg-zinc-800">
                                    <button
                                        type="button"
                                        wire:click="$set('attended_before', 'yes')"
                                        class="rounded-lg px-5 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $attended_before === 'yes' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                    >
                                        Yes
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="$set('attended_before', 'no')"
                                        class="rounded-lg px-5 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $attended_before === 'no' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                    >
                                        No (First Time)
                                    </button>
                                </div>
                            </div>

                            @if ($attended_before === 'yes')
                                <div>
                                    <flux:input
                                        wire:model="years_attended"
                                        type="number"
                                        min="1"
                                        max="50"
                                        :label="__('How many years have you attended the Birthday? *')"
                                        required
                                        placeholder="e.g. 3"
                                    />
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-zinc-200 dark:border-zinc-800 pt-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Do they participate in raffles? --}}
                                <div>
                                    <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">
                                        Do you participate in the raffles at the party? *
                                    </label>
                                    <div class="inline-flex rounded-xl border border-zinc-300 dark:border-zinc-700 p-1 bg-zinc-100 dark:bg-zinc-800">
                                        <button
                                            type="button"
                                            wire:click="$set('participates_in_raffles', 'yes')"
                                            class="rounded-lg px-5 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $participates_in_raffles === 'yes' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                        >
                                            Yes
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="$set('participates_in_raffles', 'no')"
                                            class="rounded-lg px-5 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $participates_in_raffles === 'no' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                        >
                                            No
                                        </button>
                                    </div>
                                </div>

                                {{-- Have they ever won a raffle? --}}
                                <div>
                                    <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-200 mb-2">
                                        Have you ever won a raffle prize at our Birthday party? *
                                    </label>
                                    <div class="inline-flex rounded-xl border border-zinc-300 dark:border-zinc-700 p-1 bg-zinc-100 dark:bg-zinc-800">
                                        <button
                                            type="button"
                                            wire:click="$set('has_won_raffle', 'yes')"
                                            class="rounded-lg px-5 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $has_won_raffle === 'yes' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                        >
                                            Yes, I've Won
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="$set('has_won_raffle', 'no')"
                                            class="rounded-lg px-5 py-2 text-xs sm:text-sm font-semibold transition cursor-pointer {{ $has_won_raffle === 'no' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white' }}"
                                        >
                                            Not Yet
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @if ($has_won_raffle === 'yes')
                                <div>
                                    <flux:input
                                        wire:model="raffle_prize_won"
                                        :label="__('What raffle item(s) have you won? (Optional)')"
                                        placeholder="e.g. Pit Boss Smoker, Blackstone Griddle, USMC Ka-Bar, Gift Basket..."
                                    />
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-800 pt-6">
                            <flux:button type="button" variant="ghost" wire:click="previousStep" icon="arrow-left">
                                Back
                            </flux:button>
                            <flux:button type="button" variant="primary" wire:click="nextStep" icon-trailing="arrow-right" class="bg-red-700 hover:bg-red-600 text-white font-semibold px-6">
                                Next: Suggestions & Submit
                            </flux:button>
                        </div>
                    @endif

                    {{-- STEP 4: Suggestions & Submit --}}
                    @if ($currentStep === 4)
                        <div class="flex items-center gap-3 border-b border-zinc-200 dark:border-zinc-800 pb-4">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-red-700 text-amber-300 font-black text-sm shadow-xs">
                                4
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Suggestions & Ideas</h2>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">We want to make the Marines of Tahlequah and our annual Birthday celebration better every year</p>
                            </div>
                        </div>

                        <flux:textarea
                            wire:model="suggestions"
                            :label="__('Is there anything you would like to see changed or added to the Marines of Tahlequah and/or the Marines of Tahlequah Birthday? (Optional)')"
                            rows="5"
                            placeholder="Share your ideas, feedback, or suggestions for our organization or the Birthday party..."
                        />

                        <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-800 pt-6">
                            <flux:button type="button" variant="ghost" wire:click="previousStep" icon="arrow-left">
                                Back
                            </flux:button>

                            <flux:button
                                type="submit"
                                variant="primary"
                                icon="check"
                                class="bg-red-700 hover:bg-red-600 text-white font-semibold px-8 shadow-md"
                            >
                                Submit Birthday Survey
                            </flux:button>
                        </div>
                    @endif
                </div>
            </form>
        @endif
    </div>
</div>
