<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::livewire('donate', 'pages::donate.index')->name('donate');
Route::livewire('about', 'pages::about.index')->name('about.index');

// Admin Event, About & Slideshow Management (must come before dynamic {event} param)
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::livewire('events/create', 'pages::events.create')->name('events.create');
    Route::livewire('events/{event}/edit', 'pages::events.edit')->name('events.edit');
    Route::livewire('about/create', 'pages::about.create')->name('about.create');
    Route::livewire('about/{about}/edit', 'pages::about.edit')->name('about.edit');
    Route::livewire('admin/slideshow', 'pages::slideshow.index')->name('admin.slideshow');
});

// Public Events routes
Route::livewire('events', 'pages::events.index')->name('events.index');
Route::livewire('events/{event}', 'pages::events.show')->name('events.show');

// Super Admin User Role Management
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::livewire('users', 'pages::users.index')->name('users.index');
});

// Authenticated Member Dashboard
Route::middleware(['auth'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard.index')->name('dashboard');
});

if (app()->environment('local')) {
    Route::get('/dev-login', function () {
        $user = \App\Models\User::where('email', 'tiger72.jd@gmail.com')->first();
        if ($user) {
            auth()->login($user);
        }
        return redirect()->route('dashboard');
    })->name('dev.login');
}

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
