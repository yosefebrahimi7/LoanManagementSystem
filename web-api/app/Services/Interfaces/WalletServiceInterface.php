<?php

namespace App\Services\Interfaces;

use App\Models\Wallet;

interface WalletServiceInterface
{
    /**
     * Get user's wallet (user or admin based on role)
     */
    public function getWallet(\App\Models\User $user): Wallet;

    /**
     * Get wallet transactions
     */
    public function getTransactions(Wallet $wallet, int $page = 1, int $perPage = 10): array;

    /**
     * Initiate wallet recharge
     */
    public function initiateRecharge(Wallet $wallet, int $amount): array;

    /**
     * Process recharge callback from gateway
     */
    public function processRechargeCallback(array $gatewayData): mixed;
}

