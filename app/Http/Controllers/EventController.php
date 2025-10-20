<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index()
    {
        $events = Event::with(['creator', 'updater'])
            ->orderBy('event_datetime', 'desc')
            ->paginate(10);

        return Inertia::render('Events', [
            'events' => $events
        ]);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        return Inertia::render('AddEvent');
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_datetime' => 'required|date',
            'description' => 'required|string',
            'image_url' => 'nullable|url|max:500'
        ]);

        Event::create($validated);

        return redirect()->route('events.index')->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $event->load(['creator', 'updater']);
        
        return Inertia::render('EventDetail', [
            'event' => $event
        ]);
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        return Inertia::render('EditEvent', [
            'event' => $event
        ]);
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_datetime' => 'required|date',
            'description' => 'required|string',
            'image_url' => 'nullable|url|max:500'
        ]);

        $event->update($validated);

        return redirect()->route('events.index')->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted successfully!');
    }
}
