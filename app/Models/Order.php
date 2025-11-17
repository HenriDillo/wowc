<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CustomOrder;
use App\Models\Payment;
use App\Models\CancellationRequest;

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
        'recipient_name',
        'recipient_phone',
        'shipping_fee',
        'cod_fee',
        'final_payment_verified',
        'final_payment_verified_at',
        'final_payment_verified_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'required_payment_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'cod_fee' => 'decimal:2',
        'expected_restock_date' => 'date',
        'delivered_at' => 'datetime',
        'final_payment_verified' => 'boolean',
        'final_payment_verified_at' => 'datetime',
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

    public function finalPaymentVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_payment_verified_by');
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(CancellationRequest::class);
    }

    /**
     * Get the latest cancellation request for this order
     */
    public function getLatestCancellationRequest(): ?CancellationRequest
    {
        return $this->cancellationRequests()->latest()->first();
    }

    /**
     * Check if order has a pending cancellation request
     */
    public function hasPendingCancellationRequest(): bool
    {
        return $this->cancellationRequests()
            ->where('status', CancellationRequest::STATUS_REQUESTED)
            ->exists();
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
			'pending_cod' => 'Pending COD',
			'payment_rejected' => 'Payment Rejected',
			default => 'Unpaid',
		};
	}

	/**
	 * Check if order has verified payment
	 * For mixed orders, checks all child orders
	 */
	public function hasVerifiedPayment(): bool
	{
		if ($this->order_type === 'mixed' && $this->childOrders()->exists()) {
			// For mixed orders, all child orders must have verified payments
			foreach ($this->childOrders as $child) {
				if (!$child->hasVerifiedPayment()) {
					return false;
				}
			}
			return true;
		}

		// For COD orders, check if payment has been collected
		$isCod = $this->payment_method === 'COD';
		if ($isCod) {
			return $this->payment_status === 'paid';
		}

		// Check if there's a payment with approved verification
		$latestPayment = $this->payments()->latest()->first();
		if (!$latestPayment) {
			return false;
		}

		// For both bank transfers and GCash, check verification status
		return $latestPayment->isVerified();
	}

	/**
	 * Check if order has pending payment verification
	 */
	public function hasPendingPaymentVerification(): bool
	{
		if ($this->order_type === 'mixed' && $this->childOrders()->exists()) {
			// Check if any child order has pending verification
			foreach ($this->childOrders as $child) {
				if ($child->hasPendingPaymentVerification()) {
					return true;
				}
			}
			return false;
		}

		$latestPayment = $this->payments()->latest()->first();
		if (!$latestPayment) {
			return false;
		}

		// For both bank transfers and GCash, check if pending verification
		return $latestPayment->isPendingVerification();
	}

	/**
	 * Get the latest payment for this order
	 */
	public function getLatestPayment(): ?Payment
	{
		return $this->payments()->latest()->first();
	}

	/**
	 * Calculate required payment amount for mixed orders
	 */
	public function calculateRequiredPaymentForMixedOrder(): float
	{
		if ($this->order_type !== 'mixed' || !$this->childOrders()->exists()) {
			return $this->calculateRequiredPaymentAmount();
		}

		$total = 0.0;
		foreach ($this->childOrders as $child) {
			if ($child->order_type === 'standard') {
				$total += $child->total_amount; // 100% of standard
			} else {
				$total += $child->total_amount * 0.5; // 50% of backorder
			}
		}
		return $total;
	}

	/**
	 * Get valid next statuses based on current status and order type
	 * Implements forward-only status flow
	 */
	public function getValidNextStatuses(): array
	{
		$currentStatus = $this->status;
		
		// Define status flows for each order type
		$statusFlows = [
			'standard' => [
				'pending' => ['processing', 'cancelled'],
				'processing' => ['ready_to_ship', 'cancelled'],
				'ready_to_ship' => ['shipped', 'cancelled'],
				'shipped' => ['delivered', 'cancelled'],
				'delivered' => ['completed'],
				'completed' => [], // Terminal status
				'cancelled' => [], // Terminal status
			],
			'backorder' => [
				'pending' => ['processing', 'cancelled'],
				'processing' => ['ready_to_ship', 'cancelled'], // "Awaiting Stock" -> "Preparing to Ship"
				'ready_to_ship' => ['shipped', 'cancelled'],
				'shipped' => ['delivered', 'cancelled'],
				'delivered' => ['completed'],
				'completed' => [], // Terminal status
				'cancelled' => [], // Terminal status
			],
			'custom' => [
				'pending' => ['in_design', 'cancelled'],
				'in_design' => ['in_production', 'cancelled'],
				'in_production' => ['ready_for_delivery', 'cancelled'],
				'ready_for_delivery' => ['ready_to_ship', 'cancelled'],
				'ready_to_ship' => ['shipped', 'cancelled'],
				'shipped' => ['delivered', 'cancelled'],
				'delivered' => ['completed'],
				'completed' => [], // Terminal status
				'cancelled' => [], // Terminal status
			],
			'mixed' => [
				'pending' => ['processing', 'cancelled'],
				'processing' => ['ready_to_ship', 'cancelled'],
				'ready_to_ship' => ['shipped', 'cancelled'],
				'shipped' => ['delivered', 'cancelled'],
				'delivered' => ['completed'],
				'completed' => [], // Terminal status
				'cancelled' => [], // Terminal status
			],
		];

		$flow = $statusFlows[$this->order_type] ?? $statusFlows['standard'];
		
		// Return valid next statuses, or empty array if current status not found
		return $flow[$currentStatus] ?? [];
	}

	/**
	 * Check if a status transition is valid (forward-only)
	 */
	public function canTransitionTo(string $newStatus): bool
	{
		// Allow staying in the same status
		if ($newStatus === $this->status) {
			return true;
		}

		// Check if the new status is in the valid next statuses
		return in_array($newStatus, $this->getValidNextStatuses());
	}

	/**
	 * Check if this order requires final payment verification (50% upfront orders)
	 */
	public function requiresFinalPaymentVerification(): bool
	{
		return $this->order_type === self::TYPE_BACKORDER 
			|| $this->order_type === self::TYPE_CUSTOM 
			|| ($this->order_type === self::TYPE_MIXED && $this->remaining_balance > 0);
	}

	/**
	 * Check if final payment has been verified
	 */
	public function hasFinalPaymentVerified(): bool
	{
		if (!$this->requiresFinalPaymentVerification()) {
			return true; // No verification needed for orders that don't require it
		}

		// For mixed orders, check all child orders
		if ($this->order_type === self::TYPE_MIXED && $this->childOrders()->exists()) {
			foreach ($this->childOrders as $child) {
				if ($child->requiresFinalPaymentVerification() && !$child->final_payment_verified) {
					return false;
				}
			}
			return true;
		}

		return (bool) $this->final_payment_verified;
	}

	/**
	 * Check if order can be cancelled based on business rules
	 */
	public function canBeCancelled(): bool
	{
		// Already cancelled
		if ($this->status === self::STATUS_CANCELLED) {
			return false;
		}

		// Already completed
		if ($this->status === self::STATUS_COMPLETED) {
			return false;
		}

		// Check order type and status
		switch ($this->order_type) {
			case self::TYPE_STANDARD:
				// Standard orders: can cancel if pending payment, pending verification, or processing (not yet shipped)
				$allowedStatuses = [self::STATUS_PENDING, 'pending_verification', self::STATUS_PROCESSING];
				if (in_array($this->status, $allowedStatuses)) {
					return true;
				}
				// If shipped, cannot cancel (should use return process)
				if (in_array($this->status, ['shipped', 'delivered'])) {
					return false;
				}
				// If ready_to_ship but not yet shipped, check if it's actually packed
				if ($this->status === 'ready_to_ship') {
					// Allow cancellation if not yet shipped (no tracking number or not marked as shipped)
					return empty($this->tracking_number);
				}
				return false;

			case self::TYPE_BACKORDER:
			case self::TYPE_CUSTOM:
				// Backorder/Custom (50% DP): can cancel only if procurement/production has not started
				// For backorders: check if any items are in_progress or fulfilled
				if ($this->order_type === self::TYPE_BACKORDER) {
					$hasStartedProcurement = $this->items()
						->where('is_backorder', true)
						->whereIn('backorder_status', [\App\Models\OrderItem::BO_IN_PROGRESS, \App\Models\OrderItem::BO_FULFILLED])
						->exists();
					if ($hasStartedProcurement) {
						return false;
					}
				}
				// For custom orders: check if production has started
				if ($this->order_type === self::TYPE_CUSTOM) {
					$customOrder = $this->customOrders()->first();
					if ($customOrder && in_array($customOrder->status, [CustomOrder::STATUS_IN_PRODUCTION, CustomOrder::STATUS_COMPLETED])) {
						return false;
					}
				}
				// Can cancel if status is pending or processing (before production/procurement starts)
				$allowedStatuses = [self::STATUS_PENDING, 'pending_verification', self::STATUS_PROCESSING];
				if (in_array($this->status, $allowedStatuses)) {
					return true;
				}
				// If in design (custom) or in_progress (backorder), check if production/procurement actually started
				if ($this->status === 'in_design' && $this->order_type === self::TYPE_CUSTOM) {
					// Still in design phase, can cancel
					return true;
				}
				// If shipped, cannot cancel
				if (in_array($this->status, ['shipped', 'delivered'])) {
					return false;
				}
				return false;

			case self::TYPE_MIXED:
				// Mixed orders: can cancel individual items if they haven't been processed
				// For the order itself, check if any items can be cancelled
				// This is handled at item level, but we allow cancellation if order is still in early stages
				$allowedStatuses = [self::STATUS_PENDING, 'pending_verification', self::STATUS_PROCESSING];
				if (in_array($this->status, $allowedStatuses)) {
					return true;
				}
				// If shipped, cannot cancel entire order
				if (in_array($this->status, ['shipped', 'delivered'])) {
					return false;
				}
				return false;

			default:
				return false;
		}
	}

	/**
	 * Get cancellation reason message based on order state
	 */
	public function getCancellationDenialReason(): ?string
	{
		if ($this->status === self::STATUS_CANCELLED) {
			return 'Order is already cancelled.';
		}
		if ($this->status === self::STATUS_COMPLETED) {
			return 'Order is already completed.';
		}
		if (in_array($this->status, ['shipped', 'delivered'])) {
			return 'Order has already been shipped. Please use the Return Process instead.';
		}
		if ($this->order_type === self::TYPE_BACKORDER) {
			$hasStartedProcurement = $this->items()
				->where('is_backorder', true)
				->whereIn('backorder_status', [\App\Models\OrderItem::BO_IN_PROGRESS, \App\Models\OrderItem::BO_FULFILLED])
				->exists();
			if ($hasStartedProcurement) {
				return 'Procurement has already started for this backorder. Cancellation is not allowed.';
			}
		}
		if ($this->order_type === self::TYPE_CUSTOM) {
			$customOrder = $this->customOrders()->first();
			if ($customOrder && in_array($customOrder->status, [CustomOrder::STATUS_IN_PRODUCTION, CustomOrder::STATUS_COMPLETED])) {
				return 'Production has already started for this custom order. Cancellation is not allowed.';
			}
		}
		return null;
	}
}

