<?php

use App\Mail\NewEventPostedMail;
use App\Models\BirthdayAttendee;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('public birthday survey is off by default and loads once enabled by admin', function () {
    expect(SiteSetting::isBirthdaySurveyEnabled())->toBeFalse();
    $this->get(route('birthday.index'))->assertStatus(404);

    SiteSetting::setBirthdaySurveyEnabled(true);

    $response = $this->get(route('birthday.index'));

    $response->assertStatus(200);
    $response->assertSee('Birthday');
    $response->assertSee('Survey');
});

test('marine veteran can submit birthday sign-in form with optional middle name, rank, attendance years, raffle status, and suggestions', function () {
    SiteSetting::setBirthdaySurveyEnabled(true);

    Livewire::test('pages::birthday.index')
        ->set('first_name', 'John')
        ->set('middle_name', '')
        ->set('last_name', 'Miller')
        ->set('notify_future_events', 'yes')
        ->set('preferred_contact_method', 'both')
        ->set('phone', '918-555-1775')
        ->set('email', 'john.miller@example.com')
        ->set('address', '100 Marine Way')
        ->set('city', 'Tahlequah')
        ->set('state', 'OK')
        ->set('zip', '74464')
        ->set('attendee_type', 'marine')
        ->set('service_status', 'veteran')
        ->set('rank', 'GySgt')
        ->set('attended_before', 'yes')
        ->set('years_attended', 5)
        ->set('participates_in_raffles', 'yes')
        ->set('has_won_raffle', 'no')
        ->set('suggestions', 'More tables near the stage!')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $attendee = BirthdayAttendee::first();
    expect($attendee)->not->toBeNull()
        ->and($attendee->full_name)->toBe('John Miller')
        ->and($attendee->notify_future_events)->toBeTrue()
        ->and($attendee->preferred_contact_method)->toBe('both')
        ->and($attendee->attendee_type)->toBe('marine')
        ->and($attendee->service_status)->toBe('veteran')
        ->and($attendee->branch)->toBe('USMC')
        ->and($attendee->rank)->toBe('GySgt')
        ->and($attendee->attended_before)->toBeTrue()
        ->and($attendee->years_attended)->toBe(5)
        ->and($attendee->participates_in_raffles)->toBeTrue()
        ->and($attendee->has_won_raffle)->toBeFalse()
        ->and($attendee->suggestions)->toBe('More tables near the stage!');
});

test('choosing email or phone for future event updates requires the matching contact field', function () {
    SiteSetting::setBirthdaySurveyEnabled(true);

    // Choosing email without providing email fails validation
    Livewire::test('pages::birthday.index')
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('notify_future_events', 'yes')
        ->set('preferred_contact_method', 'email')
        ->set('email', '')
        ->set('phone', '918-555-0000')
        ->set('address', '100 Main St')
        ->set('attendee_type', 'marine')
        ->set('service_status', 'veteran')
        ->set('rank', 'Cpl')
        ->call('submit')
        ->assertHasErrors(['email']);

    // Choosing phone without providing phone fails validation
    Livewire::test('pages::birthday.index')
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('notify_future_events', 'yes')
        ->set('preferred_contact_method', 'phone')
        ->set('phone', '')
        ->set('email', 'john@example.com')
        ->set('address', '100 Main St')
        ->set('attendee_type', 'marine')
        ->set('service_status', 'veteran')
        ->set('rank', 'Cpl')
        ->call('submit')
        ->assertHasErrors(['phone']);

    // Choosing 'no' for notifications makes both phone and email optional and allows stepping through all 4 steps
    Livewire::test('pages::birthday.index')
        ->assertSet('currentStep', 1)
        ->set('first_name', 'Mark')
        ->set('last_name', 'Smith')
        ->set('address', '500 Oak St')
        ->set('notify_future_events', 'no')
        ->set('phone', '')
        ->set('email', '')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 2)
        ->set('attendee_type', 'marine')
        ->set('service_status', 'veteran')
        ->set('rank', 'Sgt')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 3)
        ->set('attended_before', 'no')
        ->set('participates_in_raffles', 'no')
        ->set('has_won_raffle', 'no')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 4)
        ->set('suggestions', 'Great event!')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);
});

test('other branch veteran and event donor can submit birthday sign-in form', function () {
    SiteSetting::setBirthdaySurveyEnabled(true);

    Livewire::test('pages::birthday.index')
        ->set('first_name', 'James')
        ->set('middle_name', 'D')
        ->set('last_name', 'DeShazer')
        ->set('notify_future_events', 'yes')
        ->set('preferred_contact_method', 'phone')
        ->set('phone', '918-555-0199')
        ->set('address', '200 Cherokee Ave')
        ->set('attendee_type', 'other_branch')
        ->set('branch', 'Army')
        ->set('service_status', 'veteran')
        ->set('rank', 'SGT')
        ->set('is_donor', true)
        ->set('donation_details', 'Pit Boss smokers and Blackstone griddles, plus website support')
        ->set('attended_before', 'yes')
        ->set('years_attended', 8)
        ->set('participates_in_raffles', 'yes')
        ->set('has_won_raffle', 'yes')
        ->set('raffle_prize_won', 'Ka-Bar Knife')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $attendee = BirthdayAttendee::first();
    expect($attendee->full_name)->toBe('James D DeShazer')
        ->and($attendee->branch)->toBe('Army')
        ->and($attendee->rank)->toBe('SGT')
        ->and($attendee->is_donor)->toBeTrue()
        ->and($attendee->donation_details)->toContain('Pit Boss smokers')
        ->and($attendee->has_won_raffle)->toBeTrue()
        ->and($attendee->raffle_prize_won)->toBe('Ka-Bar Knife');
});

test('posting a new event automatically sends email notifications to opted-in attendees', function () {
    Mail::fake();

    $admin = User::factory()->create(['role' => 'admin']);

    // Opted in via email
    BirthdayAttendee::create([
        'first_name' => 'John',
        'last_name' => 'Marine',
        'email' => 'john@marines.org',
        'phone' => '918-555-1111',
        'address' => '123 Semper Fi Ln',
        'notify_future_events' => true,
        'preferred_contact_method' => 'email',
        'attendee_type' => 'marine',
        'service_status' => 'veteran',
        'branch' => 'USMC',
        'rank' => 'Sgt',
    ]);

    // Opted in via phone only (should not get email)
    BirthdayAttendee::create([
        'first_name' => 'Bob',
        'last_name' => 'PhoneOnly',
        'email' => 'bob@marines.org',
        'phone' => '918-555-2222',
        'address' => '456 Ridge Rd',
        'notify_future_events' => true,
        'preferred_contact_method' => 'phone',
        'attendee_type' => 'marine',
        'service_status' => 'veteran',
        'branch' => 'USMC',
        'rank' => 'Cpl',
    ]);

    Livewire::actingAs($admin)
        ->test('pages::events.create')
        ->set('event_name', '251st Marine Corps Birthday Ball')
        ->set('event_datetime', '2026-11-10 18:00:00')
        ->set('description', 'Annual birthday celebration and raffle.')
        ->call('save')
        ->assertHasNoErrors();

    Mail::assertSent(NewEventPostedMail::class, function (NewEventPostedMail $mail) {
        return $mail->hasTo('john@marines.org')
            && $mail->event->event_name === '251st Marine Corps Birthday Ball';
    });

    Mail::assertNotSent(NewEventPostedMail::class, function (NewEventPostedMail $mail) {
        return $mail->hasTo('bob@marines.org');
    });
});

test('admin can turn birthday survey on and off, hiding public links and blocking access when off', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // By default, survey is OFF
    expect(SiteSetting::isBirthdaySurveyEnabled())->toBeFalse();
    $this->get(route('birthday.index'))->assertStatus(404);
    $this->get(route('home'))->assertDontSee(route('birthday.index'));

    // Admin toggles it ON via the Admin Birthday page
    Livewire::actingAs($admin)
        ->test('pages::birthday.admin')
        ->assertSet('surveyEnabled', false)
        ->call('toggleSurveyStatus')
        ->assertSet('surveyEnabled', true);

    expect(SiteSetting::isBirthdaySurveyEnabled())->toBeTrue();

    // Public birthday page now loads and homepage shows the Birthday Survey link
    $this->get(route('birthday.index'))->assertStatus(200);
    $this->get(route('home'))->assertSee(route('birthday.index'));

    // Admin toggles it back OFF when the event is over
    Livewire::actingAs($admin)
        ->test('pages::birthday.admin')
        ->call('toggleSurveyStatus')
        ->assertSet('surveyEnabled', false);

    expect(SiteSetting::isBirthdaySurveyEnabled())->toBeFalse();
    $this->get(route('birthday.index'))->assertStatus(404);
});

test('submissions record event_year and date/time, and admin can filter between years', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Create a 2025 entry
    $attendee2025 = BirthdayAttendee::create([
        'event_year' => 2025,
        'first_name' => 'Chesty',
        'last_name' => 'Puller',
        'address' => '1775 Tun Tavern Rd',
        'notify_future_events' => false,
        'attendee_type' => 'marine',
        'service_status' => 'veteran',
        'branch' => 'USMC',
        'rank' => 'LtGen',
    ]);

    // Create a 2026 entry
    $attendee2026 = BirthdayAttendee::create([
        'event_year' => 2026,
        'first_name' => 'Dan',
        'last_name' => 'Daly',
        'address' => '1918 Belleau Wood',
        'notify_future_events' => false,
        'attendee_type' => 'marine',
        'service_status' => 'veteran',
        'branch' => 'USMC',
        'rank' => 'SgtMaj',
    ]);

    expect($attendee2025->event_year)->toBe(2025)
        ->and($attendee2025->created_at)->not->toBeNull()
        ->and($attendee2026->event_year)->toBe(2026)
        ->and($attendee2026->created_at)->not->toBeNull();

    // Filter by 2025 in Admin view
    Livewire::actingAs($admin)
        ->test('pages::birthday.admin')
        ->set('filterYear', '2025')
        ->assertSee('Chesty Puller')
        ->assertDontSee('Dan Daly')
        ->set('filterYear', '2026')
        ->assertSee('Dan Daly')
        ->assertDontSee('Chesty Puller');
});

