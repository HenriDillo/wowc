<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'type',
        'quantity',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Item associated with this transaction
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Employee who performed the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get transaction type label
     */
    public function getTypeLabel()
    {
        return $this->type === 'in' ? 'Stock In' : 'Stock Out';
    }
}
