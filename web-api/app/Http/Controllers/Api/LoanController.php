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

class LoanController extends Controller
{
    public function __construct(
        private LoanService $loanService,
        private LoanStatisticsService $statisticsService
    ) {}

    /**
     * Get user's loans
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
