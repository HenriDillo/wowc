<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'status',
    ];

    public function customItems()
    {
        return $this->hasMany(CustomCartItem::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}


