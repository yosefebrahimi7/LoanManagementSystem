<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WalletRechargeRequest;
use App\Models\Wallet;
use App\Services\Interfaces\WalletServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(
        private WalletServiceInterface $walletService
    ) {}

    /**
     * Get user's wallet balance
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $wallet = $this->walletService->getWallet($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $wallet->id,
                    'balance' => $wallet->balance,
                    'formatted_balance' => $wallet->formatted_balance,
                    'currency' => $wallet->currency,
                    'is_shared' => $wallet->is_shared,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت اطلاعات کیف پول',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get wallet transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $wallet = $this->walletService->getWallet($user);

            $page = $request->get('page', 1);
            $perPage = $request->get('limit', 10);

            $result = $this->walletService->getTransactions($wallet, $page, $perPage);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'meta' => $result['meta'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت تراکنش‌ها',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initiate wallet recharge
     */
    public function recharge(WalletRechargeRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $wallet = $this->walletService->getWallet($user);

            $result = $this->walletService->initiateRecharge($wallet, $request->validated()['amount']);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'خطا در شروع فرآیند شارژ',
            ], 400);
        }
    }

    /**
     * Handle wallet recharge callback from payment gateway
     */
    public function callback(Request $request)
    {
        return $this->walletService->processRechargeCallback($request->all());
    }
}
