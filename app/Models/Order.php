<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CustomOrder;
use App\Models\Payment;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_order_id',
        'order_type',
        'status',
        'total_amount',
        'required_payment_amount',
        'remaining_balance',
        'payment_method',
        'payment_status',
        'back_order_status',
        'expected_restock_date',
        'tracking_number',
        'carrier',
        'delivered_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'required_payment_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'expected_restock_date' => 'date',
        'delivered_at' => 'datetime',
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
    const TYPE_MIXED = 'mixed';

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

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }

    public function childOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_order_id');
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

    public function payments(): HasMany
	{
		return $this->hasMany(Payment::class);
	}

	/**
	 * Determine the payment percentage required based on order type
	 * Standard orders: 100%, Back Orders & Custom Orders: 50%
	 */
	public function getRequiredPaymentPercentage(): float
	{
		if ($this->order_type === self::TYPE_BACKORDER || $this->order_type === self::TYPE_CUSTOM) {
			return 0.5; // 50% down payment
		}
		return 1.0; // 100% for standard orders
	}

	/**
	 * Calculate the amount customer must pay at checkout
	 */
	public function calculateRequiredPaymentAmount(): float
	{
		$percentage = $this->getRequiredPaymentPercentage();
		return (float) ($this->total_amount * $percentage);
	}

	/**
	 * Get the remaining balance after a partial payment
	 */
	public function getRemainingBalance(): float
	{
		$requiredAmount = $this->calculateRequiredPaymentAmount();
		$paidAmount = (float) $this->payments()->where('status', 'paid')->sum('amount');
		return max(0, $requiredAmount - $paidAmount);
	}

	/**
	 * Check if the order is fully paid
	 */
	public function isFullyPaid(): bool
	{
		return $this->payment_status === 'paid';
	}

	/**
	 * Check if the order is partially paid (for backorder/custom orders)
	 */
	public function isPartiallyPaid(): bool
	{
		return $this->payment_status === 'partially_paid';
	}

	/**
	 * Get payment status label for display
	 */
	public function getPaymentStatusLabel(): string
	{
		return match($this->payment_status) {
			'paid' => 'Fully Paid ✓',
			'partially_paid' => 'Partially Paid',
			'pending_verification' => 'Pending Verification',
			default => 'Unpaid',
		};
	}
}

