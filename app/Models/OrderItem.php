<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_id',
        'quantity',
        'price',
        'subtotal',
        'is_backorder',
        'backorder_status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'is_backorder' => 'boolean',
        'backorder_status' => 'string',
    ];

    // Backorder lifecycle states for order items
    const BO_PENDING = 'pending_stock';
    const BO_IN_PROGRESS = 'in_progress';
    const BO_FULFILLED = 'fulfilled';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function isPendingBackorder(): bool
    {
        return $this->is_backorder && $this->backorder_status === self::BO_PENDING;
    }

    public function isInProgressBackorder(): bool
    {
        return $this->is_backorder && $this->backorder_status === self::BO_IN_PROGRESS;
    }

    public function markFulfilled(): void
    {
        if ($this->is_backorder) {
            $this->backorder_status = self::BO_FULFILLED;
            $this->save();
        }
    }
}


