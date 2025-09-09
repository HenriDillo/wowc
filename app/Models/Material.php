<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quantity',  
        'unit',        
        'status',     
        'is_hidden',   
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    // Optional: automatically update status based on quantity
    public function updateStatus()
    {
        if ($this->quantity <= 0) {
            $this->status = 'Out of Stock';
        } elseif ($this->quantity <= 5) { // example threshold
            $this->status = 'Low Stock';
        } else {
            $this->status = 'Available';
        }
        $this->save();
    }
}
