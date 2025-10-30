<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class SlideshowController extends Controller
{
    /**
     * Get the path to the MarinesPictures directory
     */
    private function getPicturesPath(): string
    {
        return public_path('MarinesPictures');
    }

    /**
     * Get list of all slideshow photos
     */
    public function index()
    {
        $photos = $this->getPhotos();

        return Inertia::render('Slideshow/Index', [
            'photos' => $photos,
        ]);
    }

    /**
     * Get list of photos for API
     */
    public function list()
    {
        return response()->json([
            'photos' => $this->getPhotos(),
        ]);
    }

    /**
     * Upload a new photo to the slideshow
     */
    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,gif|max:10240', // 10MB max
        ]);

        $file = $request->file('photo');
        $filename = time().'_'.$file->getClientOriginalName();

        $file->move($this->getPicturesPath(), $filename);

        return back()->with('success', 'Photo uploaded successfully!');
    }

    /**
     * Delete a photo from the slideshow
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        $filename = $request->input('filename');
        $filePath = $this->getPicturesPath().'/'.$filename;

        // Security check: ensure the filename doesn't contain path traversal
        if (basename($filename) !== $filename) {
            return back()->withErrors(['error' => 'Invalid filename.']);
        }

        if (File::exists($filePath)) {
            File::delete($filePath);

            return back()->with('success', 'Photo deleted successfully!');
        }

        return back()->withErrors(['error' => 'Photo not found.']);
    }

    /**
     * Get all photos from the MarinesPictures directory
     */
    private function getPhotos(): array
    {
        $picturesPath = $this->getPicturesPath();

        if (! File::exists($picturesPath)) {
            return [];
        }

        $files = File::files($picturesPath);
        $photos = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            // Only include image files
            if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $filename)) {
                $photos[] = [
                    'filename' => $filename,
                    'url' => '/MarinesPictures/'.$filename,
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                ];
            }
        }

        // Sort by filename
        usort($photos, function ($a, $b) {
            return strcmp($a['filename'], $b['filename']);
        });

        return $photos;
    }
}
