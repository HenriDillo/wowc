<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CancellationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'requested_by',
        'status',
        'handled_by',
        'refund_amount',
        'refund_method',
        'notes',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
    ];

    // Status constants
    const STATUS_REQUESTED = 'Cancellation Requested';
    const STATUS_APPROVED = 'Cancellation Approved';
    const STATUS_REJECTED = 'Cancellation Rejected';
    const STATUS_REFUND_PROCESSING = 'Refund Processing';
    const STATUS_REFUND_COMPLETED = 'Refund Completed';
    const STATUS_CANCELLED = 'Cancelled';
    const STATUS_REFUND_FAILED = 'Refund Failed';

    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_REQUESTED,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_REFUND_PROCESSING,
            self::STATUS_REFUND_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_REFUND_FAILED,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Check if status transition is valid
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $validTransitions = [
            self::STATUS_REQUESTED => [self::STATUS_APPROVED, self::STATUS_REJECTED],
            self::STATUS_APPROVED => [self::STATUS_REFUND_PROCESSING, self::STATUS_CANCELLED, self::STATUS_REJECTED],
            self::STATUS_REFUND_PROCESSING => [self::STATUS_REFUND_COMPLETED, self::STATUS_REFUND_FAILED],
            self::STATUS_REFUND_COMPLETED => [self::STATUS_CANCELLED],
            self::STATUS_REFUND_FAILED => [self::STATUS_REFUND_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_REJECTED => [], // Terminal status
            self::STATUS_CANCELLED => [], // Terminal status
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
            self::STATUS_REQUESTED => 'Cancellation Requested',
            self::STATUS_APPROVED => 'Cancellation Approved',
            self::STATUS_REJECTED => 'Cancellation Rejected',
            self::STATUS_REFUND_PROCESSING => 'Refund Processing',
            self::STATUS_REFUND_COMPLETED => 'Refund Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REFUND_FAILED => 'Refund Failed',
            default => $this->status,
        };
    }

    /**
     * Check if cancellation request is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_REQUESTED;
    }

    /**
     * Check if cancellation request is approved
     */
    public function isApproved(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_REFUND_PROCESSING,
            self::STATUS_REFUND_COMPLETED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Check if cancellation request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if refund is required
     */
    public function requiresRefund(): bool
    {
        return $this->refund_amount !== null && $this->refund_amount > 0;
    }
}
