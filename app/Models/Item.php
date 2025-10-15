<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stock',
        'price',
        'visible',
        'category',
        'description',
        'status',
        'release_date',
        'restock_date',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'release_date' => 'date',
        'restock_date' => 'date',
    ];

    protected $appends = [
        'photo_url',
    ];

    /**
     * Photos attached to the item.
     */
    public function photos()
    {
        return $this->hasMany(ItemPhoto::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        $primary = $this->relationLoaded('photos') ? $this->photos->first() : $this->photos()->first();
        if ($primary) {
            return $primary->url;
        }
        return asset('images/welcome-bg.jpg');
    }

    public function isPreorder(): bool
    {
        return (string) ($this->status ?? '') === 'pre_order';
    }

    public function preorderLimit(): int
    {
        return (int) (config('app.preorder_max', 2));
    }

    public function isBackorder(): bool
    {
        return (string) ($this->status ?? '') === 'back_order';
    }
}


