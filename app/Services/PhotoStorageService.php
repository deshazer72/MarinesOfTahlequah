<?php

namespace App\Services;

use App\Models\UploadedPhoto;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PhotoStorageService
{
    /**
     * Store an uploaded photo directly in the database and return its public URL.
     *
     * @return string Public URL path, e.g. "/photos/123"
     */
    public static function store(TemporaryUploadedFile|UploadedFile $file, ?int $userId = null): string
    {
        $rawContent = file_get_contents($file->getRealPath());
        $mime = $file->getMimeType() ?: 'image/jpeg';
        $originalName = $file->getClientOriginalName();

        $processedData = self::optimizeImage($rawContent, $file->getRealPath(), $mime);

        $photo = UploadedPhoto::create([
            'filename' => $originalName,
            'mime_type' => $processedData['mime'],
            'file_size' => strlen($processedData['binary']),
            'image_data' => base64_encode($processedData['binary']),
            'created_by' => $userId,
        ]);

        return '/photos/'.$photo->id;
    }

    /**
     * Optimize, fix orientation, and resize large images using GD.
     */
    protected static function optimizeImage(string $rawContent, string $filePath, string $mime): array
    {
        if (! extension_loaded('gd')) {
            return ['binary' => $rawContent, 'mime' => $mime];
        }

        try {
            $image = @imagecreatefromstring($rawContent);
            if (! $image) {
                return ['binary' => $rawContent, 'mime' => $mime];
            }

            // Correct EXIF orientation for photos taken on phones
            if (function_exists('exif_read_data') && in_array($mime, ['image/jpeg', 'image/jpg'], true)) {
                $exif = @exif_read_data($filePath);
                if (! empty($exif['Orientation'])) {
                    $image = match ($exif['Orientation']) {
                        3 => imagerotate($image, 180, 0),
                        6 => imagerotate($image, -90, 0),
                        8 => imagerotate($image, 90, 0),
                        default => $image,
                    };
                }
            }

            $origWidth = imagesx($image);
            $origHeight = imagesy($image);
            $maxDimension = 1920;

            // Resize if dimensions exceed maxDimension
            if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
                if ($origWidth >= $origHeight) {
                    $newWidth = $maxDimension;
                    $newHeight = (int) round(($origHeight / $origWidth) * $maxDimension);
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int) round(($origWidth / $origHeight) * $maxDimension);
                }

                $resized = imagescale($image, $newWidth, $newHeight, IMG_BILINEAR_FIXED);
                if ($resized) {
                    $image = $resized;
                }
            }

            // Encode to JPEG or PNG
            ob_start();
            if ($mime === 'image/png') {
                imagepng($image, null, 6);
                $finalMime = 'image/png';
            } else {
                imagejpeg($image, null, 85);
                $finalMime = 'image/jpeg';
            }
            $binary = ob_get_clean();

            return [
                'binary' => $binary ?: $rawContent,
                'mime' => $finalMime,
            ];
        } catch (\Throwable) {
            return ['binary' => $rawContent, 'mime' => $mime];
        }
    }
}
