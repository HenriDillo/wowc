<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
	use HasFactory;

	protected $fillable = [
		'order_id',
		'method',
		'amount',
		'status',
		'transaction_id',
		'proof_image',
		'verified_by',
		'verification_status',
		'verification_notes',
		'verified_at',
	];

	protected $casts = [
		'verified_at' => 'datetime',
	];

	public function order(): BelongsTo
	{
		return $this->belongsTo(Order::class);
	}

	public function verifier(): BelongsTo
	{
		return $this->belongsTo(User::class, 'verified_by');
	}

	/**
	 * Check if payment is verified (approved)
	 */
	public function isVerified(): bool
	{
		return $this->verification_status === 'approved';
	}

	/**
	 * Check if payment is pending verification
	 */
	public function isPendingVerification(): bool
	{
		return $this->verification_status === 'pending';
	}

	/**
	 * Check if payment is rejected
	 */
	public function isRejected(): bool
	{
		return $this->verification_status === 'rejected';
	}
}


