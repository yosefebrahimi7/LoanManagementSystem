<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoanRequest;
use App\Http\Requests\LoanApprovalRequest;
use App\Http\Resources\LoanResource;
use App\Http\Resources\LoanStatisticsResource;
use App\Models\Loan;
use App\Services\LoanService;
use App\Services\LoanStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 *   name="Loans",
 *   description="Loan management endpoints"
 * )
 */
class LoanController extends Controller
{
    public function __construct(
        private LoanService $loanService,
        private LoanStatisticsService $statisticsService
    ) {}

    /**
     * Get user's loans
     * 
     * @OA\Get(
     *   path="/api/loans",
     *   tags={"Loans"},
     *   summary="Get user's loans",
     *   operationId="getUserLoans",
     *   security={{"sanctum": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="List of user loans",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *     )
     *   )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!Gate::allows('viewAny', Loan::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $loans = $this->loanService->getUserLoans($user);

        return response()->json([
            'success' => true,
            'data' => LoanResource::collection($loans)
        ]);
    }

    /**
     * Create a new loan request
     * 
     * @OA\Post(
     *   path="/api/loans",
     *   tags={"Loans"},
     *   summary="Create loan request",
     *   operationId="createLoan",
     *   security={{"sanctum": {}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"amount", "term_months", "start_date"},
     *       @OA\Property(property="amount", type="integer", example=10000000),
     *       @OA\Property(property="term_months", type="integer", example=12),
     *       @OA\Property(property="interest_rate", type="number", format="float", example=14.5),
     *       @OA\Property(property="start_date", type="string", format="date", example="2025-11-01")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Loan request created",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string"),
     *       @OA\Property(property="data", type="object")
     *     )
     *   )
     * )
     */
    public function store(LoanRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $loan = $this->loanService->createLoanRequest(
            $user,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Loan request submitted successfully',
            'data' => new LoanResource($loan)
        ], 201);
    }

    /**
     * Get specific loan details
     * 
     * @OA\Get(
     *   path="/api/loans/{id}",
     *   tags={"Loans"},
     *   summary="Get loan details",
     *   operationId="getLoan",
     *   security={{"sanctum": {}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Loan details",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="data", type="object")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Loan not found")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $loan = $this->loanService->getLoanById($id, $user);

        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new LoanResource($loan)
        ]);
    }

    /**
     * Get all loans for admin
     * 
     * @OA\Get(
     *   path="/api/admin/loans",
     *   tags={"Loans", "Admin"},
     *   summary="Get all loans (Admin only)",
     *   operationId="getAllLoans",
     *   security={{"sanctum": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="List of all loans",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *     )
     *   ),
     *   @OA\Response(response=403, description="Unauthorized - Admin access required")
     * )
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $loans = $this->loanService->getAllLoans();

        return response()->json([
            'success' => true,
            'data' => LoanResource::collection($loans)
        ]);
    }

    /**
     * Approve or reject a loan (Admin only)
     * 
     * @OA\Post(
     *   path="/api/admin/loans/{id}/approve",
     *   tags={"Loans", "Admin"},
     *   summary="Approve or reject loan",
     *   operationId="approveLoan",
     *   security={{"sanctum": {}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"action"},
     *       @OA\Property(property="action", type="string", enum={"approve", "reject"}, example="approve"),
     *       @OA\Property(property="rejection_reason", type="string")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Loan processed successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string"),
     *       @OA\Property(property="data", type="object")
     *     )
     *   ),
     *   @OA\Response(response=403, description="Unauthorized"),
     *   @OA\Response(response=404, description="Loan not found")
     * )
     */
    public function approve(LoanApprovalRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $loan = Loan::find($id);
        
        if (!$loan) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found'
            ], 404);
        }

        if (!$this->loanService->canApproveLoan($loan)) {
            return response()->json([
                'success' => false,
                'message' => 'Loan is not pending approval'
            ], 400);
        }

        $result = $this->loanService->processLoanApproval(
            $loan,
            $user,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => new LoanResource($result['loan'])
        ]);
    }

    /**
     * Get loan statistics (Admin only)
     * 
     * @OA\Get(
     *   path="/api/admin/loans/stats",
     *   tags={"Loans", "Admin"},
     *   summary="Get loan statistics (Admin only)",
     *   operationId="getLoanStats",
     *   security={{"sanctum": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="Loan statistics",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         @OA\Property(property="total_loans", type="integer", example=150),
     *         @OA\Property(property="pending_loans", type="integer", example=25),
     *         @OA\Property(property="approved_loans", type="integer", example=100),
     *         @OA\Property(property="rejected_loans", type="integer", example=15),
         *         @OA\Property(property="active_loans", type="integer", example=75),
         *         @OA\Property(property="total_amount", type="integer", example=1500000000),
         *         @OA\Property(property="total_paid", type="integer", example=500000000)
     *       )
     *     )
     *   ),
     *   @OA\Response(response=403, description="Unauthorized - Admin access required")
     * )
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $stats = $this->statisticsService->getStatistics();

        return response()->json([
            'success' => true,
            'data' => new LoanStatisticsResource($stats)
        ]);
    }
}
