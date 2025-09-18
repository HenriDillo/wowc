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
        return \Illuminate\Support\Facades\Storage::disk('public')->url($normalized);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}


