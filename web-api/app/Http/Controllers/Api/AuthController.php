<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Interfaces\AuthServiceInterface;
use App\Traits\ApiResponseTrait;
use App\Exceptions\AuthException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *   name="Authentication",
 *   description="User authentication endpoints"
 * )
 */
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
     * 
     * @OA\Post(
     *   path="/api/auth/register",
     *   tags={"Authentication"},
     *   summary="Register a new user",
     *   operationId="register",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"firstName", "lastName", "email", "password"},
     *       @OA\Property(property="firstName", type="string", example="John"),
     *       @OA\Property(property="lastName", type="string", example="Doe"),
     *       @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *       @OA\Property(property="password", type="string", format="password", example="password123"),
     *       @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="User registered successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="ثبت نام با موفقیت انجام شد"),
     *       @OA\Property(property="data", type="object")
     *     )
     *   )
     * )
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
            $statusCode = is_numeric($e->getCode()) ? (int)$e->getCode() : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    /**
     * Login user
     * 
     * @OA\Post(
     *   path="/api/auth/login",
     *   tags={"Authentication"},
     *   summary="Login user",
     *   operationId="login",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email", "password"},
     *       @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *       @OA\Property(property="password", type="string", format="password", example="password123")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Login successful",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="ورود با موفقیت انجام شد"),
     *       @OA\Property(property="data", type="object")
     *     )
     *   ),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=403, description="Account inactive")
     * )
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
            $statusCode = is_numeric($e->getCode()) ? (int)$e->getCode() : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    /**
     * Logout user
     * 
     * @OA\Post(
     *   path="/api/auth/logout",
     *   tags={"Authentication"},
     *   summary="Logout user",
     *   operationId="logout",
     *   security={{"sanctum": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="Logout successful",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="خروج با موفقیت انجام شد")
     *     )
     *   )
     * )
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
     * 
     * @OA\Get(
     *   path="/api/auth/me",
     *   tags={"Authentication"},
     *   summary="Get authenticated user",
     *   operationId="getProfile",
     *   security={{"sanctum": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="User profile",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="data", type="object")
     *     )
     *   )
     * )
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
     * 
     * @OA\Post(
     *   path="/api/auth/refresh",
     *   tags={"Authentication"},
     *   summary="Refresh authentication token",
     *   operationId="refreshToken",
     *   security={{"sanctum": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="Token refreshed successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="توکن با موفقیت به‌روزرسانی شد"),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="token", type="string"),
     *         @OA\Property(property="refreshToken", type="string")
     *       )
     *     )
     *   )
     * )
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