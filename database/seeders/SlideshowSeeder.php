<?php

namespace Database\Seeders;

use App\Models\SlideshowImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SlideshowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->orWhere('role', 'superadmin')->first();

        // Standard default featured photos
        $defaultPhotos = [
            ['image_url' => '/MarinesPictures/IMG_8884.jpg', 'title' => 'Marines of Tahlequah Honor Guard', 'caption' => 'Dedicated service and community support.'],
            ['image_url' => '/MarinesPictures/IMG_8885.jpg', 'title' => 'Community Gathering', 'caption' => 'Tahlequah veterans uniting for service.'],
            ['image_url' => '/MarinesPictures/pic1.jpg', 'title' => 'Cherokee County Veterans', 'caption' => 'Semper Fidelis - Always Faithful.'],
            ['image_url' => '/MarinesPictures/pic2.jpg', 'title' => 'Veterans Day Ceremony', 'caption' => 'Honoring our brothers and sisters in arms.'],
            ['image_url' => '/MarinesPictures/pic3.jpg', 'title' => 'Toys for Tots Drive', 'caption' => 'Giving back to children and families in need.'],
            ['image_url' => '/MarinesPictures/IMG_8936.JPG', 'title' => 'Raffles for Vets', 'caption' => 'Supporting veterans in Cherokee County.'],
        ];

        // Also check if public/MarinesPictures directory exists and add any remaining images
        $dir = public_path('MarinesPictures');
        if (File::isDirectory($dir)) {
            $files = File::files($dir);
            $order = 0;
            foreach ($files as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $url = '/MarinesPictures/' . $file->getFilename();
                    $order++;
                    SlideshowImage::firstOrCreate(
                        ['image_url' => $url],
                        [
                            'title' => 'Gallery Photo #' . $order,
                            'caption' => 'Marines of Tahlequah community photo',
                            'sort_order' => $order,
                            'is_active' => true,
                            'created_by' => $admin?->id,
                        ]
                    );
                }
            }
        } else {
            foreach ($defaultPhotos as $i => $item) {
                SlideshowImage::firstOrCreate(
                    ['image_url' => $item['image_url']],
                    [
                        'title' => $item['title'],
                        'caption' => $item['caption'],
                        'sort_order' => $i + 1,
                        'is_active' => true,
                        'created_by' => $admin?->id,
                    ]
                );
            }
        }
    }
}
