<?php

namespace App\Repositories;

use App\Models\Wallet;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WalletRepository implements WalletRepositoryInterface
{
    protected $model;

    public function __construct(Wallet $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new wallet
     */
    public function create(array $data): Wallet
    {
        return $this->model->create($data);
    }

    /**
     * Find wallet by ID
     */
    public function find(int $id): ?Wallet
    {
        return $this->model->find($id);
    }

    /**
     * Find wallet by user ID
     */
    public function findByUserId(int $userId): ?Wallet
    {
        return Cache::remember("wallet.user.{$userId}", 300, function () use ($userId) {
            return $this->model->where('user_id', $userId)->first();
        });
    }

    /**
     * Update wallet
     */
    public function update(int $id, array $data): ?Wallet
    {
        $wallet = $this->model->find($id);
        if ($wallet) {
            $wallet->update($data);
            // Clear cache when wallet is updated
            $this->clearWalletCache($wallet);
        }
        return $wallet;
    }

    /**
     * Delete wallet
     */
    public function delete(int $id): bool
    {
        $wallet = $this->model->find($id);
        if ($wallet) {
            $this->clearWalletCache($wallet);
            return $wallet->delete();
        }
        return false;
    }

    /**
     * Get all wallets
     */
    public function all(): Collection
    {
        return Cache::remember('wallets.all', 300, function () {
            return $this->model->with(['user', 'transactions'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Get wallets with transactions
     */
    public function getWalletsWithTransactions(): Collection
    {
        return Cache::remember('wallets.with_transactions', 300, function () {
            return $this->model->with(['transactions' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        });
    }

    /**
     * Update wallet balance (atomic operation)
     */
    public function updateBalance(int $walletId, int $amount): bool
    {
        return DB::transaction(function () use ($walletId, $amount) {
            $wallet = $this->model->find($walletId);
            if (!$wallet) {
                return false;
            }

            $wallet->increment('balance', $amount);
            $this->clearWalletCache($wallet);
            
            return true;
        });
    }

    /**
     * Get wallet balance
     */
    public function getBalance(int $walletId): int
    {
        return Cache::remember("wallet.balance.{$walletId}", 60, function () use ($walletId) {
            $wallet = $this->model->find($walletId);
            return $wallet ? $wallet->balance : 0;
        });
    }

    /**
     * Clear wallet-related cache
     */
    private function clearWalletCache(Wallet $wallet): void
    {
        Cache::forget('wallets.all');
        Cache::forget("wallet.user.{$wallet->user_id}");
        Cache::forget("wallet.balance.{$wallet->id}");
        Cache::forget('wallets.with_transactions');
    }
}
