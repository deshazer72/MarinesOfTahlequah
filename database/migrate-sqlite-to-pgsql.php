<?php

/**
 * SQLite to PostgreSQL Data Migration Script for Marines of Tahlequah
 *
 * Usage:
 *   php database/migrate-sqlite-to-pgsql.php
 *
 * This script connects to the existing database/database.sqlite file and imports
 * users, events, and about content into the PostgreSQL database configured in your .env.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Event;
use App\Models\About;
use App\Models\SlideshowImage;

$sqlitePath = __DIR__ . '/database.sqlite';

if (!file_exists($sqlitePath)) {
    echo "No SQLite database found at {$sqlitePath}. Nothing to migrate.\n";
    exit(0);
}

echo "Starting migration from SQLite to PostgreSQL...\n";

// Connect to SQLite directly
$sqlite = new PDO("sqlite:" . $sqlitePath);
$sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Migrate Users
echo "Migrating users...\n";
$stmt = $sqlite->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $u) {
    User::updateOrCreate(
        ['email' => $u['email']],
        [
            'name' => $u['name'],
            'password' => $u['password'],
            'role' => $u['role'] ?? 'user',
            'email_verified_at' => $u['email_verified_at'],
            'created_at' => $u['created_at'],
            'updated_at' => $u['updated_at'],
        ]
    );
    echo " - Imported user: {$u['email']} ({$u['role']})\n";
}

// 2. Migrate Events
echo "Migrating events...\n";
try {
    $stmt = $sqlite->query("SELECT * FROM events");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as $e) {
        Event::updateOrCreate(
            ['event_name' => $e['event_name'], 'event_datetime' => $e['event_datetime']],
            [
                'description' => $e['description'],
                'image_url' => $e['image_url'],
                'created_by' => $e['created_by'] ?? null,
                'updated_by' => $e['updated_by'] ?? null,
                'created_at' => $e['created_at'],
                'updated_at' => $e['updated_at'],
            ]
        );
        echo " - Imported event: {$e['event_name']}\n";
    }
} catch (Exception $ex) {
    echo "Notice: Could not query events from SQLite: " . $ex->getMessage() . "\n";
}

// 3. Migrate About Content
echo "Migrating about items...\n";
try {
    $stmt = $sqlite->query("SELECT * FROM abouts");
    $abouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($abouts as $a) {
        About::updateOrCreate(
            ['title' => $a['title']],
            [
                'content' => $a['content'],
                'image_url' => $a['image_url'],
                'order' => $a['order'] ?? 0,
                'is_published' => (bool) ($a['is_published'] ?? 1),
                'user_id' => $a['user_id'] ?? User::first()?->id,
                'created_at' => $a['created_at'],
                'updated_at' => $a['updated_at'],
            ]
        );
        echo " - Imported about: {$a['title']}\n";
    }
} catch (Exception $ex) {
    echo "Notice: Could not query abouts from SQLite: " . $ex->getMessage() . "\n";
}

// 4. Migrate Slideshow Images
echo "Migrating slideshow images...\n";
try {
    $stmt = $sqlite->query("SELECT * FROM slideshow_images");
    $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($slides as $s) {
        SlideshowImage::updateOrCreate(
            ['image_url' => $s['image_url']],
            [
                'title' => $s['title'] ?? null,
                'caption' => $s['caption'] ?? null,
                'sort_order' => $s['sort_order'] ?? 0,
                'is_active' => (bool) ($s['is_active'] ?? 1),
                'created_by' => $s['created_by'] ?? null,
                'created_at' => $s['created_at'],
                'updated_at' => $s['updated_at'],
            ]
        );
        echo " - Imported slide: {$s['image_url']}\n";
    }
} catch (Exception $ex) {
    echo "Notice: Could not query slideshow_images from SQLite: " . $ex->getMessage() . "\n";
}

echo "Migration completed successfully!\n";
