<?php

namespace App\Http\Middleware;

use App\Services\AuthRateLimitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthRateLimitMiddleware
{
    protected $rateLimitService;

    public function __construct(AuthRateLimitService $rateLimitService)
    {
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = 'login'): Response
    {
        $ip = $request->ip();
        $email = $request->input('email');

        switch ($type) {
            case 'login':
                if ($email && $this->rateLimitService->tooManyLoginAttempts($email)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'تعداد تلاش‌های ورود بیش از حد مجاز است. لطفا بعدا تلاش کنید.',
                        'retry_after' => $this->rateLimitService->getLockoutTimeRemaining($email),
                    ], 429);
                }
                break;

            case 'register':
                if ($this->rateLimitService->tooManyRegistrationAttempts($ip)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'تعداد تلاش‌های ثبت نام بیش از حد مجاز است. لطفا بعدا تلاش کنید.',
                    ], 429);
                }
                break;

            case 'refresh':
                if ($this->rateLimitService->tooManyTokenRefreshAttempts($ip)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'تعداد تلاش‌های به‌روزرسانی توکن بیش از حد مجاز است. لطفا بعدا تلاش کنید.',
                    ], 429);
                }
                break;
        }

        return $next($request);
    }
}