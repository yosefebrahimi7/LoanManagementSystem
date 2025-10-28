<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'currency',
        'is_shared',
    ];

    protected $casts = [
        'balance' => 'integer',
        'is_shared' => 'boolean',
    ];

    /**
     * Get the user that owns the wallet
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all wallet transactions (ledger)
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get balance in readable format
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance / 100, 2);
    }

    /**
     * Check if this is a shared admin wallet
     */
    public function isAdminSharedWallet(): bool
    {
        return $this->is_shared === true;
    }

    /**
     * Scope to get shared admin wallet
     */
    public function scopeSharedAdminWallet($query)
    {
        return $query->where('is_shared', true)->where('user_id', null);
    }

    /**
     * Scope to get user wallets
     */
    public function scopeUserWallets($query)
    {
        return $query->where('is_shared', false)->whereNotNull('user_id');
    }
}
