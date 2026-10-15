<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('birthday_attendees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address');
            $table->string('city')->nullable();
            $table->string('state', 50)->nullable()->default('OK');
            $table->string('zip', 20)->nullable();

            // Future Event Contact Preferences
            $table->boolean('notify_future_events')->default(true);
            // 'email', 'phone', 'both'
            $table->string('preferred_contact_method', 30)->nullable();

            // Connection / Service Background
            // 'marine', 'other_branch', 'family_honor', 'supporter'
            $table->string('attendee_type')->default('marine');
            // 'active', 'veteran', 'civilian'
            $table->string('service_status')->nullable();
            // e.g. 'USMC', 'Army', 'Navy', 'Air Force', 'Coast Guard', 'Space Force', 'National Guard'
            $table->string('branch')->nullable();
            // Current rank (if active) or last rank (if veteran)
            $table->string('rank')->nullable();

            // Family member attending in honor of a Marine
            $table->boolean('attending_in_honor')->default(false);
            $table->string('honoring_marine_name')->nullable();
            $table->string('honoring_relationship')->nullable();

            // Event Attendance History
            $table->boolean('attended_before')->default(false);
            $table->unsignedSmallInteger('years_attended')->nullable();

            // Donor / Event Support
            $table->boolean('is_donor')->default(false);
            $table->text('donation_details')->nullable();

            // Raffles & Winner Tracking
            $table->boolean('participates_in_raffles')->default(false);
            $table->boolean('has_won_raffle')->default(false);
            $table->string('raffle_prize_won')->nullable();

            // Feedback & Suggestions
            $table->text('suggestions')->nullable();

            // Metadata
            $table->unsignedSmallInteger('event_year')->default(2026);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birthday_attendees');
    }
};
