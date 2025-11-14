<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',        
        'stock',
        'reorder_level',
        'is_hidden',   
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    /**
     * Stock transactions for this material
     */
    public function transactions()
    {
        return $this->hasMany(MaterialStockTransaction::class);
    }
}
