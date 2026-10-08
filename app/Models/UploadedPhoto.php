<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadedPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'mime_type',
        'file_size',
        'image_data',
        'created_by',
    ];

    /**
     * The user who uploaded this photo.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the public URL to view this photo.
     */
    public function getUrlAttribute(): string
    {
        return '/photos/'.$this->id;
    }
}
