<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'path',
    ];

    protected $appends = [
        'url',
    ];

    /**
     * Accessor to get the public URL for this photo, handling legacy and new paths.
     */
    public function getUrlAttribute(): string
    {
        $storedPath = (string) ($this->path ?? '');
        // Normalize legacy paths that might include a leading 'public/'
        $normalized = preg_replace('#^public/#', '', $storedPath);
        $normalized = ltrim($normalized ?? '', '/');
        // If already a full URL, return as-is
        if (preg_match('#^https?://#i', $normalized)) {
            return $normalized;
        }
        // If file exists on public disk, generate a URL. If the public/storage link is missing,
        // fall back to a streamed media endpoint to ensure cross-machine compatibility (Windows/dev boxes).
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if ($normalized !== '' && $disk->exists($normalized)) {
            $hasSymlink = is_link(public_path('storage')) || file_exists(public_path('storage'));
            if ($hasSymlink) {
                return $disk->url($normalized);
            }
            return url('/media/' . $normalized);
        }
        // Fallback to a public images placeholder
        return asset('images/login-bg.jpg');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}


