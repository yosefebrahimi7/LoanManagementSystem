<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\Interfaces\UserServiceInterface;
use App\Traits\ApiResponseTrait;
use App\Exceptions\AuthException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 *   name="Users",
 *   description="User management endpoints for admin"
 * )
 */
class UserController extends Controller
{
    use ApiResponseTrait;

    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users (Admin only)
     * 
     * @OA\Get(
     *   path="/api/users",
     *   summary="Get list of users (Admin only)",
     *   tags={"Users"},
     *   security={{"sanctum": {}}},
     *   @OA\Response(
     *     response=200,
     *     description="List of users",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
     *     )
     *   ),
     *   @OA\Response(response=403, description="Forbidden - Admin access required")
     * )
     */
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', \App\Models\User::class);
        
        $users = $this->userService->getAllUsers();
        
        return $this->successResponse(
            UserResource::collection($users),
            'لیست کاربران دریافت شد'
        );
    }

    /**
     * Display the specified user (Admin only)
     * 
     * @OA\Get(
     *   path="/api/users/{id}",
     *   summary="Get user by ID (Admin only)",
     *   tags={"Users"},
     *   security={{"sanctum": {}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User details",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="data", ref="#/components/schemas/User")
     *     )
     *   ),
     *   @OA\Response(response=404, description="User not found"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            Gate::authorize('view', $user);

            return $this->successResponse(
                new UserResource($user),
                'اطلاعات کاربر دریافت شد'
            );
        } catch (AuthException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Store a newly created user (Admin only)
     * 
     * @OA\Post(
     *   path="/api/users",
     *   summary="Create new user (Admin only)",
     *   tags={"Users"},
     *   security={{"sanctum": {}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"firstName", "lastName", "email", "password", "role"},
     *       @OA\Property(property="first_name", type="string", example="John"),
     *       @OA\Property(property="last_name", type="string", example="Doe"),
     *       @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *       @OA\Property(property="password", type="string", format="password", example="password123"),
     *       @OA\Property(property="password_confirmation", type="string", example="password123"),
     *       @OA\Property(property="role", type="integer", example=1),
     *       @OA\Property(property="is_active", type="boolean", example=true)
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="User created successfully"
     *   ),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=403, description="Forbidden - Admin access required")
     * )
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return $this->successResponse(
            new UserResource($user),
            'کاربر با موفقیت ایجاد شد',
            201
        );
    }

    /**
     * Update the specified user (Admin only)
     * 
     * @OA\Put(
     *   path="/api/users/{id}",
     *   summary="Update user (Admin only)",
     *   tags={"Users"},
     *   security={{"sanctum": {}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     @OA\JsonContent(
     *       @OA\Property(property="first_name", type="string", example="John"),
     *       @OA\Property(property="last_name", type="string", example="Doe"),
     *       @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *       @OA\Property(property="password", type="string", format="password"),
     *       @OA\Property(property="password_confirmation", type="string"),
     *       @OA\Property(property="role", type="integer", example=1),
     *       @OA\Property(property="is_active", type="boolean", example=true)
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User updated successfully"
     *   ),
     *   @OA\Response(response=404, description="User not found"),
     *   @OA\Response(response=422, description="Validation error"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->updateUser($id, $request->validated());

            return $this->successResponse(
                new UserResource($user),
                'کاربر با موفقیت به‌روزرسانی شد'
            );
        } catch (AuthException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Remove the specified user (Admin only)
     * 
     * @OA\Delete(
     *   path="/api/users/{id}",
     *   summary="Delete user (Admin only)",
     *   tags={"Users"},
     *   security={{"sanctum": {}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User deleted successfully"
     *   ),
     *   @OA\Response(response=404, description="User not found"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            Gate::authorize('delete', $user);
            
            $this->userService->deleteUser($id);

            return $this->successResponse(
                null,
                'کاربر با موفقیت حذف شد'
            );
        } catch (AuthException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Toggle user status (Active/Inactive)
     * 
     * @OA\Patch(
     *   path="/api/users/{id}/toggle-status",
     *   summary="Toggle user status (Admin only)",
     *   tags={"Users"},
     *   security={{"sanctum": {}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User status toggled successfully"
     *   ),
     *   @OA\Response(response=404, description="User not found"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            Gate::authorize('update', $user);
            
            $updatedUser = $this->userService->toggleUserStatus($id);
            $isActive = $updatedUser->is_active;

            return $this->successResponse(
                new UserResource($updatedUser),
                $isActive ? 'کاربر فعال شد' : 'کاربر غیرفعال شد'
            );
        } catch (AuthException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        }
    }
}

