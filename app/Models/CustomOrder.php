<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'custom_name',
        'description',
        'customization_details',
        'reference_image_path',
        'quantity',
        'price_estimate',
        'status',
        'admin_notes',
        'estimated_completion_date',
        'rejection_note',
    ];

    protected $casts = [
        'customization_details' => 'array',
        'quantity' => 'integer',
        'price_estimate' => 'decimal:2',
        'estimated_completion_date' => 'date',
    ];

    const STATUS_PENDING_REVIEW = 'pending_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IN_PRODUCTION = 'in_production';
    const STATUS_COMPLETED = 'completed';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}