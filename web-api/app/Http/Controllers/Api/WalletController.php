<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WalletRechargeRequest;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;
use App\Models\Wallet;
use App\Services\Interfaces\WalletServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *   name="Wallet",
 *   description="Wallet management endpoints"
 * )
 */
class WalletController extends Controller
{
    public function __construct(
        private WalletServiceInterface $walletService
    ) {}

    /**
     * Get user's wallet balance
     * 
     * @OA\Get(
     *   path="/api/wallet",
     *   tags={"Wallet"},
     *   summary="Get wallet balance",
     *   operationId="getWallet",
     *   security={{"sanctum": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="Wallet information",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="balance", type="integer", example=0),
     *         @OA\Property(property="formatted_balance", type="string", example="0.00"),
     *         @OA\Property(property="currency", type="string", example="IRR"),
     *         @OA\Property(property="is_shared", type="boolean", example=false)
     *       )
     *     )
     *   )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $wallet = $this->walletService->getWallet($user);

            return response()->json([
                'success' => true,
                'data' => new WalletResource($wallet),
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
     * 
     * @OA\Get(
     *   path="/api/wallet/transactions",
     *   tags={"Wallet"},
     *   summary="Get wallet transactions",
     *   operationId="getWalletTransactions",
     *   security={{"sanctum": {}}},
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     @OA\Schema(type="integer", default=1)
     *   ),
     *   @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     @OA\Schema(type="integer", default=10)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Wallet transactions",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *       @OA\Property(property="meta", type="object")
     *     )
     *   )
     * )
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
                'data' => WalletTransactionResource::collection($result['data']),
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
     * 
     * @OA\Post(
     *   path="/api/wallet/recharge",
     *   tags={"Wallet"},
     *   summary="Initiate wallet recharge",
     *   operationId="rechargeWallet",
     *   security={{"sanctum": {}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"amount", "method"},
     *       @OA\Property(property="amount", type="integer", example=1000000, description="Amount in Tomans (min: 10,000, max: 2,000,000,000)"),
     *       @OA\Property(property="method", type="string", example="zarinpal")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Payment initiated",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         @OA\Property(property="payment_url", type="string"),
     *         @OA\Property(property="authority", type="string"),
     *         @OA\Property(property="transaction_id", type="integer")
     *       )
     *     )
     *   )
     * )
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
     * 
     * @OA\Post(
     *   path="/api/wallet/callback",
     *   tags={"Wallet"},
     *   summary="Wallet recharge callback",
     *   operationId="walletRechargeCallback",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="Authority", type="string"),
     *       @OA\Property(property="Status", type="string")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Callback processed"
     *   )
     * )
     */
    public function callback(Request $request)
    {
        return $this->walletService->processRechargeCallback($request->all());
    }
}
