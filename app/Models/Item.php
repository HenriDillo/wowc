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
    ];

    protected $casts = [
        'visible' => 'boolean',
    ];

    /**
     * Photos attached to the item.
     */
    public function photos()
    {
        return $this->hasMany(ItemPhoto::class);
    }
}


