<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'installment_number',
        'amount_due',
        'principal_amount',
        'interest_amount',
        'penalty_amount',
        'paid_amount',
        'due_date',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'amount_due' => 'integer',
        'principal_amount' => 'integer',
        'interest_amount' => 'integer',
        'penalty_amount' => 'integer',
        'paid_amount' => 'integer',
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    /**
     * Schedule statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';

    /**
     * Get the loan that owns this schedule
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Get all payments for this schedule
     */
    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    /**
     * Check if installment is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date < now() && $this->status !== self::STATUS_PAID;
    }

    /**
     * Check if installment is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->amount_due;
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAmountAttribute(): int
    {
        return max(0, $this->amount_due - $this->paid_amount);
    }
}
