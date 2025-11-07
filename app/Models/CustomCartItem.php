<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomCartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'custom_name',
        'description',
        'customization_details',
        'reference_image_path',
        'quantity',
    ];

    protected $casts = [
        'customization_details' => 'array',
        'quantity' => 'integer',
    ];
}