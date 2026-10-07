<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->orWhere('role', 'superadmin')->first();

        Event::updateOrCreate(
            ['event_name' => 'Raffles for Vets - Fall Fundraiser'],
            [
                'event_datetime' => '2026-10-24 18:00:00',
                'description' => 'Join us for our annual Fall Raffle for Vets! Great prizes, smoked barbecue, and fellowship in support of Cherokee County veterans and their families.',
                'image_url' => '/MarinesPictures/IMG_8936.JPG',
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ]
        );

        Event::updateOrCreate(
            ['event_name' => '251st Marine Corps Birthday Ball'],
            [
                'event_datetime' => '2026-11-10 18:00:00',
                'description' => 'Celebrate the 251st Birthday of the United States Marine Corps with dinner, the traditional cake-cutting ceremony, and honoring our oldest and youngest Marines present.',
                'image_url' => '/MarinesPictures/IMG_8884.jpg',
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ]
        );

        Event::updateOrCreate(
            ['event_name' => 'Veterans Day Community Ceremony'],
            [
                'event_datetime' => '2026-11-11 10:00:00',
                'description' => 'Annual Veterans Day commemoration honoring all military veterans who served in the United States Armed Forces. Refreshments served following the ceremony.',
                'image_url' => '/MarinesPictures/pic2.jpg',
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ]
        );

        Event::updateOrCreate(
            ['event_name' => 'Toys for Tots & Holiday Food Drive'],
            [
                'event_datetime' => '2026-12-05 11:00:00',
                'description' => 'Collecting new, unwrapped toys and non-perishable food items for local families and children across Cherokee County for the holiday season.',
                'image_url' => '/MarinesPictures/pic3.jpg',
                'created_by' => $admin?->id,
                'updated_by' => $admin?->id,
            ]
        );
    }
}
