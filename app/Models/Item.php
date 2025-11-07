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
        'restock_date',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'stock' => 'integer',
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

    public function isBackorder(): bool
    {
        $s = strtolower((string) ($this->status ?? ''));
        return $s === 'back_order';
    }

    protected static function booted()
    {
        static::saving(function (Item $item) {
            // If the status was explicitly changed by the caller, do not auto-overwrite it.
            if ($item->isDirty('status')) {
                return;
            }

            // Auto-set status based on stock level (admin manual overrides preserved)
            $stock = (int) ($item->stock ?? 0);
            if ($stock <= 0) {
                $item->status = 'back_order';
            } else {
                $item->status = 'in_stock';
            }
        });
    }
}


