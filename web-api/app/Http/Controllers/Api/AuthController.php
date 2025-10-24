<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Interfaces\AuthServiceInterface;
use App\Traits\ApiResponseTrait;
use App\Exceptions\AuthException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->authService->register($data);

            return $this->successResponse([
                'user' => $result['user'],
                'token' => $result['token'],
                'refreshToken' => $result['refreshToken'],
            ], 'ثبت نام با موفقیت انجام شد', 201);
        } catch (AuthException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();
            $result = $this->authService->login($credentials);

            return $this->successResponse([
                'user' => $result['user'],
                'token' => $result['token'],
                'refreshToken' => $result['refreshToken'],
            ], 'ورود با موفقیت انجام شد');
        } catch (AuthException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());

            return $this->successResponse(null, 'خروج با موفقیت انجام شد');
        } catch (\Exception $e) {
            return $this->errorResponse('خطا در خروج از سیستم');
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        try {
            $user = $this->authService->getAuthenticatedUser($request->user());

            return $this->successResponse(['user' => $user]);
        } catch (\Exception $e) {
            return $this->errorResponse('خطا در دریافت اطلاعات کاربر');
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        try {
            $result = $this->authService->refreshToken($request->user());

            return $this->successResponse([
                'token' => $result['token'],
                'refreshToken' => $result['refreshToken'],
            ], 'توکن با موفقیت به‌روزرسانی شد');
        } catch (\Exception $e) {
            return $this->errorResponse('خطا در به‌روزرسانی توکن');
        }
    }
}