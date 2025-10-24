<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Success response
     */
    public static function success($data = null, string $message = 'عملیات با موفقیت انجام شد', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response
     */
    public static function error(string $message = 'خطا در انجام عملیات', int $statusCode = 500, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    public static function validationError($errors, string $message = 'اطلاعات وارد شده نامعتبر است'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'دسترسی غیرمجاز'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'شما به این بخش دسترسی ندارید'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'اطلاعات مورد نظر یافت نشد'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Conflict response
     */
    public static function conflict(string $message = 'این اطلاعات قبلا ثبت شده است'): JsonResponse
    {
        return self::error($message, 409);
    }
}
