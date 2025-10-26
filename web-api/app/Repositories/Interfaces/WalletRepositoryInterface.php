<?php

namespace App\Repositories\Interfaces;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;

interface WalletRepositoryInterface
{
    /**
     * Create a new wallet
     */
    public function create(array $data): Wallet;

    /**
     * Find wallet by ID
     */
    public function find(int $id): ?Wallet;

    /**
     * Find wallet by user ID
     */
    public function findByUserId(int $userId): ?Wallet;

    /**
     * Update wallet
     */
    public function update(int $id, array $data): ?Wallet;

    /**
     * Delete wallet
     */
    public function delete(int $id): bool;

    /**
     * Get all wallets
     */
    public function all(): Collection;

    /**
     * Get wallets with transactions
     */
    public function getWalletsWithTransactions(): Collection;

    /**
     * Update wallet balance
     */
    public function updateBalance(int $walletId, int $amount): bool;

    /**
     * Get wallet balance
     */
    public function getBalance(int $walletId): int;
}
