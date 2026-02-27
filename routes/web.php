<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SlideshowController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/donate', function () {
    return Inertia::render('Donate');
})->name('donate');

// About routes
Route::get('/about', [AboutController::class, 'index'])->name('about.index');

// Admin-only about management routes
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/about/create', [AboutController::class, 'create'])->name('about.create');
    Route::post('/about', [AboutController::class, 'store'])->name('about.store');
    Route::get('/about/{about}/edit', [AboutController::class, 'edit'])->name('about.edit');
    Route::put('/about/{about}', [AboutController::class, 'update'])->name('about.update');
    Route::delete('/about/{about}', [AboutController::class, 'destroy'])->name('about.destroy');
});

// Event routes
Route::get('/events', [EventController::class, 'index'])->name('events.index');

// Admin-only event management routes (must come before {event} route)
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
});

// This must come after the create route
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Super admin only - User management routes
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Slideshow management routes
    Route::get('/slideshow', [SlideshowController::class, 'index'])->name('slideshow.index');
    Route::post('/slideshow/upload', [SlideshowController::class, 'upload'])->name('slideshow.upload');
    Route::delete('/slideshow', [SlideshowController::class, 'destroy'])->name('slideshow.destroy');
});

// Public API to get slideshow photos
Route::get('/api/slideshow/photos', [SlideshowController::class, 'list'])->name('slideshow.list');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
