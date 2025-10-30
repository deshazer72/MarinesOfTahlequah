<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up test photos directory
    $path = public_path('MarinesPictures');
    if (File::exists($path)) {
        foreach (File::files($path) as $file) {
            if (str_starts_with($file->getFilename(), 'test_')) {
                File::delete($file);
            }
        }
    }
});

afterEach(function () {
    // Clean up test photos after each test
    $path = public_path('MarinesPictures');
    if (File::exists($path)) {
        foreach (File::files($path) as $file) {
            if (str_starts_with($file->getFilename(), 'test_')) {
                File::delete($file);
            }
        }
    }
});

test('superadmin can access slideshow management page', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    actingAs($superadmin)
        ->get('/slideshow')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Slideshow/Index'));
});

test('admin cannot access slideshow management page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    actingAs($admin)
        ->get('/slideshow')
        ->assertForbidden();
});

test('regular user cannot access slideshow management page', function () {
    $user = User::factory()->create(['role' => 'user']);

    actingAs($user)
        ->get('/slideshow')
        ->assertForbidden();
});

test('guest cannot access slideshow management page', function () {
    get('/slideshow')
        ->assertRedirect('/login');
});

test('anyone can access slideshow photos list API', function () {
    get('/api/slideshow/photos')
        ->assertOk()
        ->assertJson(['photos' => []]);
});

test('superadmin can upload a photo', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $file = UploadedFile::fake()->image('test_photo.jpg');

    actingAs($superadmin)
        ->post('/slideshow/upload', [
            'photo' => $file,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // Verify the file was saved
    $path = public_path('MarinesPictures');
    $files = File::files($path);
    $testFiles = array_filter($files, fn ($file) => str_contains($file->getFilename(), 'test_photo.jpg'));

    expect(count($testFiles))->toBeGreaterThan(0);
});

test('superadmin cannot upload non-image files', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $file = UploadedFile::fake()->create('test_document.pdf', 100);

    actingAs($superadmin)
        ->post('/slideshow/upload', [
            'photo' => $file,
        ])
        ->assertSessionHasErrors(['photo']);
});

test('admin cannot upload photos', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $file = UploadedFile::fake()->image('test_photo.jpg');

    actingAs($admin)
        ->post('/slideshow/upload', [
            'photo' => $file,
        ])
        ->assertForbidden();
});

test('superadmin can delete a photo', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    // Create a test file
    $path = public_path('MarinesPictures');
    if (! File::exists($path)) {
        File::makeDirectory($path, 0755, true);
    }

    $filename = 'test_delete.jpg';
    File::put($path.'/'.$filename, 'test content');

    actingAs($superadmin)
        ->delete('/slideshow', [
            'filename' => $filename,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // Verify the file was deleted
    expect(File::exists($path.'/'.$filename))->toBeFalse();
});

test('superadmin cannot delete files with path traversal', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    actingAs($superadmin)
        ->delete('/slideshow', [
            'filename' => '../../../etc/passwd',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors();
});

test('admin cannot delete photos', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    actingAs($admin)
        ->delete('/slideshow', [
            'filename' => 'test.jpg',
        ])
        ->assertForbidden();
});

test('slideshow photos list includes all image files', function () {
    // Create some test files
    $path = public_path('MarinesPictures');
    if (! File::exists($path)) {
        File::makeDirectory($path, 0755, true);
    }

    File::put($path.'/test_1.jpg', 'test content 1');
    File::put($path.'/test_2.png', 'test content 2');
    File::put($path.'/test_3.txt', 'not an image');

    $response = get('/api/slideshow/photos')
        ->assertOk();

    $photos = $response->json('photos');

    // Should include .jpg and .png but not .txt
    $testFiles = array_filter($photos, fn ($photo) => str_starts_with($photo['filename'], 'test_'));

    expect(count($testFiles))->toBe(2);
});
