<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'term_months',
        'interest_rate',
        'monthly_payment',
        'remaining_balance',
        'status',
        'start_date',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'integer',
        'monthly_payment' => 'integer',
        'remaining_balance' => 'integer',
        'interest_rate' => 'decimal:2',
        'start_date' => 'date',
        'approved_at' => 'date',
    ];

    /**
     * Loan statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ACTIVE = 'active';
    const STATUS_DELINQUENT = 'delinquent';
    const STATUS_PAID = 'paid';

    /**
     * Get the user that owns this loan
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved this loan
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all loan schedules
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(LoanSchedule::class)->orderBy('installment_number');
    }

    /**
     * Get all loan payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    /**
     * Check if loan is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if loan is pending approval
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
