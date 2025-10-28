<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Resources\LoanResource;
use App\Http\Resources\UserResource;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Payment callback (public routes for gateway)
Route::match(['get', 'post'], '/payment/callback', [PaymentController::class, 'callback']);
Route::match(['get', 'post'], '/wallet/callback', [WalletController::class, 'callback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    
    // Loan routes for regular users
    Route::prefix('loans')->group(function () {
        Route::get('/', [LoanController::class, 'index']);
        Route::post('/', [LoanController::class, 'store']);
        Route::get('/{id}', [LoanController::class, 'show']);
    });
    
    // Admin loan routes
    Route::prefix('admin/loans')->group(function () {
        Route::get('/', [LoanController::class, 'adminIndex']);
        Route::get('/stats', [LoanController::class, 'stats']);
        Route::post('/{id}/approve', [LoanController::class, 'approve']);
    });

    // Admin user management routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    });

    // Payment routes
    Route::prefix('payment')->group(function () {
        Route::get('/history', [PaymentController::class, 'paymentHistory']);
        Route::get('/status/{payment}', [PaymentController::class, 'paymentStatus']);
        Route::post('/loans/{loan}/initiate', [PaymentController::class, 'initiatePayment']);
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Wallet routes
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'index']);
        Route::get('/transactions', [WalletController::class, 'transactions']);
        Route::post('/recharge', [WalletController::class, 'recharge']);
    });

    // Example of using API Resources directly in routes
    Route::get('/example/loan/{id}', function (Request $request, int $id) {
        $loan = Loan::with(['user', 'schedules', 'payments', 'approvedBy'])->findOrFail($id);
        
        // Using API Resource directly
        return new LoanResource($loan);
    });

    Route::get('/example/user/{id}', function (Request $request, int $id) {
        $user = User::findOrFail($id);
        
        // Using API Resource directly
        return new UserResource($user);
    });

    // Example of using Resource Collection
    Route::get('/example/loans', function (Request $request) {
        $loans = Loan::with(['user', 'schedules', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Using Resource Collection with pagination
        return LoanResource::collection($loans);
    });
});