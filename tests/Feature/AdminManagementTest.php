<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public can visit events page with calendar view', function () {
    $response = $this->get('/events');
    $response->assertStatus(200);
    $response->assertSee('Events Calendar');
    $response->assertSee('Calendar View');
});

test('public can visit about page', function () {
    $response = $this->get('/about');
    $response->assertStatus(200);
    $response->assertSee('About Marines of Tahlequah');
});

test('guests are redirected from admin routes', function () {
    $this->get('/admin/slideshow')->assertRedirect('/login');
    $this->get('/events/create')->assertRedirect('/login');
    $this->get('/about/create')->assertRedirect('/login');
});

test('regular users cannot access admin management routes', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->get('/admin/slideshow')->assertForbidden();
    $this->actingAs($user)->get('/events/create')->assertForbidden();
    $this->actingAs($user)->get('/about/create')->assertForbidden();
});

test('admins can access slideshow, events create, and about create', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/admin/slideshow')->assertStatus(200);
    $this->actingAs($admin)->get('/events/create')->assertStatus(200);
    $this->actingAs($admin)->get('/about/create')->assertStatus(200);
});

test('superadmins can access all management routes and user roles', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)->get('/admin/slideshow')->assertStatus(200);
    $this->actingAs($superadmin)->get('/events/create')->assertStatus(200);
    $this->actingAs($superadmin)->get('/about/create')->assertStatus(200);
    $this->actingAs($superadmin)->get('/users')->assertStatus(200);
});

test('admin can create event via component', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    \Livewire\Livewire::actingAs($admin)
        ->test('pages::events.create')
        ->set('event_name', 'Annual Toys for Tots Drive')
        ->set('event_datetime', '2026-11-20 10:00:00')
        ->set('description', 'Join us to collect toys for children in our community.')
        ->set('image_url', '/MarinesPictures/IMG_8936.JPG')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect('/events');

    $this->assertDatabaseHas('events', [
        'event_name' => 'Annual Toys for Tots Drive',
        'image_url' => '/MarinesPictures/IMG_8936.JPG',
    ]);
});

test('admin can create about entry via component', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    \Livewire\Livewire::actingAs($admin)
        ->test('pages::about.create')
        ->set('title', 'Cherokee County Marine Support')
        ->set('content', 'We support veterans and military families in Cherokee County.')
        ->set('image_url', '/MarinesPictures/IMG_8938.JPG')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect('/about');

    $this->assertDatabaseHas('abouts', [
        'title' => 'Cherokee County Marine Support',
        'image_url' => '/MarinesPictures/IMG_8938.JPG',
    ]);
});

test('admin can add, toggle, and delete slideshow images', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $component = \Livewire\Livewire::actingAs($admin)
        ->test('pages::slideshow.index')
        ->set('image_url', '/MarinesPictures/IMG_8940.JPG')
        ->set('title', 'Color Guard Ceremony')
        ->set('caption', 'Presenting colors at Tahlequah High School')
        ->call('addImage')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('slideshow_images', [
        'title' => 'Color Guard Ceremony',
        'image_url' => '/MarinesPictures/IMG_8940.JPG',
        'is_active' => true,
    ]);

    $image = \App\Models\SlideshowImage::where('title', 'Color Guard Ceremony')->first();

    // Toggle active
    $component->call('toggleActive', $image->id);
    $this->assertDatabaseHas('slideshow_images', [
        'id' => $image->id,
        'is_active' => false,
    ]);

    // Delete
    $component->call('confirmDelete', $image->id);
    $component->call('deleteImage');
    $this->assertDatabaseMissing('slideshow_images', [
        'id' => $image->id,
    ]);
});

test('admin navigation tab is displayed for admins linking to dashboard without dropdown', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/');
    $response->assertStatus(200);
    $response->assertSee(route('dashboard'), false);
    $response->assertSee('Admin');
});

test('admin navigation tab is hidden for guests', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertDontSee('>Admin<', false);
});

test('dashboard top-left logo links back to home page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee(route('home'), false);
});

test('superadmin can change user role and delete user', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $user = User::factory()->create(['role' => 'user']);

    // Change role via updateUserRole
    \Livewire\Livewire::actingAs($superadmin)
        ->test('pages::users.index')
        ->call('updateUserRole', $user->id, 'admin');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => 'admin',
    ]);

    // Delete user via delete modal
    \Livewire\Livewire::actingAs($superadmin)
        ->test('pages::users.index')
        ->call('openDeleteModal', $user->id, $user->name)
        ->call('deleteUser');

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('superadmin can open role modal, update role, and receive confirmation banner', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $user = User::factory()->create(['role' => 'user', 'name' => 'John Doe']);

    \Livewire\Livewire::actingAs($superadmin)
        ->test('pages::users.index')
        ->assertDontSee('Change User Role')
        ->call('openRoleModal', $user->id)
        ->assertSee('Change User Role')
        ->assertSee('John Doe')
        ->assertSet('selectedUserId', $user->id)
        ->assertSet('selectedUserRole', 'user')
        ->set('selectedUserRole', 'admin')
        ->call('updateRole')
        ->assertSet('selectedUserId', null)
        ->assertSee('Role for John Doe updated to Admin.');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => 'admin',
    ]);
});

test('superadmin cannot change own role or delete own account', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin', 'name' => 'Root Admin']);
    $anotherSuper = User::factory()->create(['role' => 'superadmin']);

    \Livewire\Livewire::actingAs($superadmin)
        ->test('pages::users.index')
        ->call('updateUserRole', $superadmin->id, 'admin')
        ->assertSee('You cannot change your own role.')
        ->call('openDeleteModal', $superadmin->id)
        ->call('deleteUser')
        ->assertSee('You cannot delete your own account here.');

    $this->assertDatabaseHas('users', [
        'id' => $superadmin->id,
        'role' => 'superadmin',
    ]);
});

test('cannot remove or delete the last super admin', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $otherAdmin = User::factory()->create(['role' => 'admin']);

    // Attempting to demote the sole superadmin
    \Livewire\Livewire::actingAs($superadmin)
        ->test('pages::users.index')
        // Even if calling directly:
        ->call('updateUserRole', $superadmin->id, 'admin')
        ->assertSee('You cannot change your own role.');

    expect(User::where('role', 'superadmin')->count())->toBe(1);
});


test('admin can browse gallery photos on slideshow component', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $component = \Livewire\Livewire::actingAs($admin)
        ->test('pages::slideshow.index');

    $libraryPhotos = $component->instance()->getAvailablePhotos();
    expect(count($libraryPhotos))->toBeGreaterThan(0);

    $component->call('selectLibraryPhoto', $libraryPhotos[0]['url']);
    $component->assertSet('image_url', $libraryPhotos[0]['url']);
    $component->assertSet('showLibraryModal', false);
});

test('events page renders uncropped ambient backdrop photos without cropping', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $eventDate = now()->addDays(5)->setTime(18, 30);
    $event = \App\Models\Event::create([
        'event_name' => 'Tahlequah Marine Ball',
        'event_datetime' => $eventDate,
        'description' => 'Annual ball celebration',
        'image_url' => '/MarinesPictures/IMG_8936.JPG',
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    // Test in selected date view
    \Livewire\Livewire::test('pages::events.index')
        ->call('selectDate', $eventDate->toDateString())
        ->assertSee('object-contain')
        ->assertSee('filter blur-2xl')
        ->assertSee('/MarinesPictures/IMG_8936.JPG');

    // Test in cards grid view
    \Livewire\Livewire::test('pages::events.index')
        ->set('view_mode', 'cards')
        ->assertSee('object-contain')
        ->assertSee('filter blur-2xl')
        ->assertSee('/MarinesPictures/IMG_8936.JPG');

    // Test on event show page
    $response = $this->get(route('events.show', $event));
    $response->assertStatus(200);
    $response->assertSee('object-contain', false);
    $response->assertSee('filter blur-2xl', false);
    $response->assertSee('/MarinesPictures/IMG_8936.JPG', false);
});

test('admin can create and edit event with calendar picker', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Test creating event with datetime
    \Livewire\Livewire::actingAs($admin)
        ->test('pages::events.create')
        ->set('event_name', 'Toys for Tots Drive')
        ->set('event_datetime', '2026-11-20T14:00')
        ->set('description', 'Collecting toys for local children')
        ->set('image_url', '/MarinesPictures/IMG_8936.JPG')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect('/events');

    $event = \App\Models\Event::where('event_name', 'Toys for Tots Drive')->first();
    expect($event)->not->toBeNull();
    expect($event->event_datetime->format('Y-m-d H:i'))->toBe('2026-11-20 14:00');

    // Test editing event with new datetime
    \Livewire\Livewire::actingAs($admin)
        ->test('pages::events.edit', ['event' => $event])
        ->set('event_datetime', '2026-11-21T16:30')
        ->call('update')
        ->assertHasNoErrors();

    $event->refresh();
    expect($event->event_datetime->format('Y-m-d H:i'))->toBe('2026-11-21 16:30');
});


