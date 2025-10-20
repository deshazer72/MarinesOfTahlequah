<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\About;
use App\Models\User;

class AboutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user or create one if none exists
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@marinesoftahlequah.org',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
            ]);
        }

        About::create([
            'title' => 'Our Mission',
            'content' => 'The Marines of Tahlequah is dedicated to serving our community and supporting fellow veterans. We believe in the values of honor, courage, and commitment that have guided Marines throughout history.',
            'order' => 1,
            'is_published' => true,
            'user_id' => $user->id,
        ]);

        About::create([
            'title' => 'Our History',
            'content' => 'Founded by Marines who wanted to give back to the Tahlequah community, our organization has been serving local veterans and families for years. We work closely with local charities and the Cherokee Nation to support those in need.',
            'order' => 2,
            'is_published' => true,
            'user_id' => $user->id,
        ]);

        About::create([
            'title' => 'Community Impact',
            'content' => 'Through our partnerships with local organizations, we have been able to support homeless veterans, participate in the Cherokee Nation Elder Care Programs, and contribute to the Angel Tree initiative. We are grateful for the support of community partners like the Turnpike Troubadours.',
            'order' => 3,
            'is_published' => true,
            'user_id' => $user->id,
        ]);
    }
}
