<?php

namespace App\Services;

use App\Models\UploadedPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PhotoStorageService
{
    /**
     * Store an uploaded photo in Cloud Object Storage (R2/S3) if configured, or directly in the database.
     *
     * @return string Public URL path or cloud URL
     */
    public static function store(TemporaryUploadedFile|UploadedFile $file, ?int $userId = null): string
    {
        $rawContent = '';

        if (method_exists($file, 'get')) {
            try {
                $rawContent = (string) $file->get();
            } catch (\Throwable) {
            }
        }

        if (empty($rawContent) && method_exists($file, 'readStream')) {
            try {
                $stream = $file->readStream();
                if (is_resource($stream)) {
                    $rawContent = (string) stream_get_contents($stream);
                }
            } catch (\Throwable) {
            }
        }

        if (empty($rawContent)) {
            try {
                $realPath = $file->getRealPath();
                if ($realPath && file_exists($realPath)) {
                    $rawContent = (string) file_get_contents($realPath);
                }
            } catch (\Throwable) {
            }
        }

        if (empty($rawContent)) {
            try {
                $path = method_exists($file, 'path') ? $file->path() : null;
                if ($path && file_exists($path)) {
                    $rawContent = (string) file_get_contents($path);
                }
            } catch (\Throwable) {
            }
        }

        $mime = $file->getMimeType() ?: 'image/jpeg';
        $originalName = $file->getClientOriginalName();

        $processedData = self::optimizeImage($rawContent, $mime);

        // Check if Cloud Object Storage (S3 / Cloudflare R2) is configured
        $defaultDisk = config('filesystems.default');
        $s3Bucket = config('filesystems.disks.s3.bucket');
        $r2Bucket = config('filesystems.disks.r2.bucket');

        if (in_array($defaultDisk, ['s3', 'r2'], true) || ! empty($s3Bucket) || ! empty($r2Bucket)) {
            $disk = in_array($defaultDisk, ['s3', 'r2'], true) ? $defaultDisk : (! empty($r2Bucket) ? 'r2' : 's3');
            try {
                $ext = ($processedData['mime'] === 'image/png') ? 'png' : 'jpg';
                $path = 'photos/'.Str::random(40).'.'.$ext;

                // Cloudflare R2 manages visibility at bucket level (do not set per-object ACL 'visibility' => 'public')
                Storage::disk($disk)->put($path, $processedData['binary'], [
                    'ContentType' => $processedData['mime'],
                ]);

                return Storage::disk($disk)->url($path);
            } catch (\Throwable $e) {
                Log::warning('Cloud object storage upload failed, falling back to database: '.$e->getMessage());
            }
        }

        // 2. Database storage fallback (Serverless PostgreSQL)
        try {
            if (Schema::hasTable('uploaded_photos')) {
                $photo = UploadedPhoto::create([
                    'filename' => $originalName,
                    'mime_type' => $processedData['mime'],
                    'file_size' => strlen($processedData['binary']),
                    'image_data' => base64_encode($processedData['binary']),
                    'created_by' => $userId,
                ]);

                return '/photos/'.$photo->id;
            }
        } catch (\Throwable $e) {
            Log::warning('Database photo storage failed, falling back to local storage: '.$e->getMessage());
        }

        // 3. Last-resort fallback: store to local public disk
        $path = $file->store('events', 'public');

        return '/storage/'.$path;
    }

    /**
     * Optimize, fix orientation, and resize large images using GD.
     */
    protected static function optimizeImage(string $rawContent, string $mime): array
    {
        if (empty($rawContent) || ! extension_loaded('gd')) {
            return ['binary' => $rawContent, 'mime' => $mime];
        }

        try {
            $image = @imagecreatefromstring($rawContent);
            if (! $image) {
                return ['binary' => $rawContent, 'mime' => $mime];
            }

            // Correct EXIF orientation for photos taken on phones
            if (function_exists('exif_read_data') && in_array($mime, ['image/jpeg', 'image/jpg'], true)) {
                $tempPath = @tempnam(sys_get_temp_dir(), 'mot_img_');
                if ($tempPath) {
                    @file_put_contents($tempPath, $rawContent);
                    $exif = @exif_read_data($tempPath);
                    @unlink($tempPath);

                    if (! empty($exif['Orientation'])) {
                        $image = match ($exif['Orientation']) {
                            3 => imagerotate($image, 180, 0),
                            6 => imagerotate($image, -90, 0),
                            8 => imagerotate($image, 90, 0),
                            default => $image,
                        };
                    }
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
