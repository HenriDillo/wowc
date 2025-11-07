<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CustomOrder;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_type',
        'status',
        'total_amount',
        'payment_method',
        'payment_status',
        'back_order_status',
        'expected_restock_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'expected_restock_date' => 'date',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_BACKORDER = 'backorder';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Order-level backorder statuses
    const BO_PENDING = 'pending_stock';
    const BO_IN_PROGRESS = 'in_progress';
    const BO_FULFILLED = 'fulfilled';

    const TYPE_STANDARD = 'standard';
    const TYPE_BACKORDER = 'backorder';
    const TYPE_CUSTOM = 'custom';

    public static function getValidOrderTypes(): array
    {
        return [
            self::TYPE_STANDARD,
            self::TYPE_BACKORDER,
            self::TYPE_CUSTOM,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isBackorder(): bool
    {
        return $this->status === self::STATUS_BACKORDER || $this->order_type === self::TYPE_BACKORDER || ($this->back_order_status ?? null) !== null;
    }

    public function markBackorderStatus(string $status): void
    {
        $this->back_order_status = $status;
        $this->save();
    }

    public function isCustom(): bool
    {
        return $this->order_type === self::TYPE_CUSTOM;
    }

    public function customOrders(): HasMany
    {
        return $this->hasMany(CustomOrder::class);
    }
}

