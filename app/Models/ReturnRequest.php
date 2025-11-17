<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'proof_image',
        'status',
        'return_tracking_number',
        'verified_at',
        'refund_amount',
        'refund_method',
        'replacement_order_id',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'refund_amount' => 'decimal:2',
    ];

    // Status constants
    const STATUS_REQUESTED = 'Return Requested';
    const STATUS_APPROVED = 'Return Approved';
    const STATUS_REJECTED = 'Return Rejected';
    const STATUS_IN_TRANSIT = 'Return In Transit';
    const STATUS_VERIFIED = 'Return Verified';
    const STATUS_REFUND_COMPLETED = 'Refund Completed';
    const STATUS_REPLACEMENT_SHIPPED = 'Replacement Shipped';
    const STATUS_COMPLETED = 'Return Completed';

    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_REQUESTED,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_VERIFIED,
            self::STATUS_REFUND_COMPLETED,
            self::STATUS_REPLACEMENT_SHIPPED,
            self::STATUS_COMPLETED,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replacementOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'replacement_order_id');
    }

    /**
     * Check if status transition is valid
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $validTransitions = [
            self::STATUS_REQUESTED => [self::STATUS_APPROVED, self::STATUS_REJECTED],
            self::STATUS_APPROVED => [self::STATUS_IN_TRANSIT, self::STATUS_REJECTED],
            self::STATUS_IN_TRANSIT => [self::STATUS_VERIFIED],
            self::STATUS_VERIFIED => [self::STATUS_REFUND_COMPLETED, self::STATUS_REPLACEMENT_SHIPPED],
            self::STATUS_REFUND_COMPLETED => [self::STATUS_COMPLETED],
            self::STATUS_REPLACEMENT_SHIPPED => [self::STATUS_COMPLETED],
            self::STATUS_REJECTED => [], // Terminal status
            self::STATUS_COMPLETED => [], // Terminal status
        ];

        $currentStatus = $this->status;
        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }

    /**
     * Get status label for display
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_REQUESTED => 'Return Requested',
            self::STATUS_APPROVED => 'Return Approved',
            self::STATUS_REJECTED => 'Return Rejected',
            self::STATUS_IN_TRANSIT => 'Return In Transit',
            self::STATUS_VERIFIED => 'Return Verified',
            self::STATUS_REFUND_COMPLETED => 'Refund Completed',
            self::STATUS_REPLACEMENT_SHIPPED => 'Replacement Shipped',
            self::STATUS_COMPLETED => 'Return Completed',
            default => $this->status,
        };
    }
}
