<?php

namespace App\Exceptions;

use Exception;

class AuthException extends Exception
{
    protected $statusCode;

    public function __construct(string $message = 'خطا در احراز هویت', int $statusCode = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->statusCode);
    }
}
