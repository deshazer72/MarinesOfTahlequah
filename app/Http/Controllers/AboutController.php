<?php

namespace App\Http\Controllers;

use App\Models\About;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AboutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $aboutItems = About::with('user')
            ->where('is_published', true)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('About', [
            'aboutItems' => $aboutItems
        ]);
    }

    // this is a demo to show how git works 

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('About/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_url' => 'nullable|url',
            'order' => 'integer|min:0',
            'is_published' => 'boolean',
        ]);

        About::create([
            'title' => $request->title,
            'content' => $request->content,
            'image_url' => $request->image_url,
            'order' => $request->order ?? 0,
            'is_published' => $request->is_published ?? true,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('about.index')->with('message', 'About content created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(About $about)
    {
        return Inertia::render('About/Show', [
            'about' => $about->load('user')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(About $about)
    {
        return Inertia::render('About/Edit', [
            'about' => $about
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, About $about)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image_url' => 'nullable|url',
            'order' => 'integer|min:0',
            'is_published' => 'boolean',
        ]);

        $about->update([
            'title' => $request->title,
            'content' => $request->content,
            'image_url' => $request->image_url,
            'order' => $request->order ?? 0,
            'is_published' => $request->is_published ?? true,
        ]);

        return redirect()->route('about.index')->with('message', 'About content updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(About $about)
    {
        $about->delete();

        return redirect()->route('about.index')->with('message', 'About content deleted successfully!');
    }
}
