<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loan_id',
        'loan_schedule_id',
        'amount',
        'payment_method',
        'status',
        'gateway_reference',
        'gateway_response',
        'notes',
    ];

    protected $casts = [
        'amount' => 'integer',
        'gateway_response' => 'array',
    ];

    /**
     * Payment methods
     */
    const METHOD_ZARINPAL = 'zarinpal';
    const METHOD_WALLET = 'wallet';
    const METHOD_MANUAL = 'manual';

    /**
     * Payment statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Get the user that made this payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the loan for this payment
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Get the schedule for this payment
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(LoanSchedule::class);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
