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
        if (empty($this->path)) {
            return asset('images/login-bg.jpg');
        }

        // If it's already a full URL, return as-is
        if (preg_match('#^https?://#i', $this->path)) {
            return $this->path;
        }

        // For direct public folder access
        if (file_exists(public_path($this->path))) {
            return asset($this->path);
        }

        // Fallback to placeholder if file doesn't exist
        return asset('images/login-bg.jpg');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}


