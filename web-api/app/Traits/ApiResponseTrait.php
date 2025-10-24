<?php

namespace App\Traits;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Success response
     */
    protected function successResponse($data = null, string $message = 'عملیات با موفقیت انجام شد', int $statusCode = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $statusCode);
    }

    /**
     * Error response
     */
    protected function errorResponse(string $message = 'خطا در انجام عملیات', int $statusCode = 500, $errors = null): JsonResponse
    {
        return ApiResponse::error($message, $statusCode, $errors);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse($errors, string $message = 'اطلاعات وارد شده نامعتبر است'): JsonResponse
    {
        return ApiResponse::validationError($errors, $message);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'دسترسی غیرمجاز'): JsonResponse
    {
        return ApiResponse::unauthorized($message);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(string $message = 'شما به این بخش دسترسی ندارید'): JsonResponse
    {
        return ApiResponse::forbidden($message);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $message = 'اطلاعات مورد نظر یافت نشد'): JsonResponse
    {
        return ApiResponse::notFound($message);
    }

    /**
     * Conflict response
     */
    protected function conflictResponse(string $message = 'این اطلاعات قبلا ثبت شده است'): JsonResponse
    {
        return ApiResponse::conflict($message);
    }
}
