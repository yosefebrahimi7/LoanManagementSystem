<?php

namespace App\Exceptions;

use Exception;

class AuthException extends Exception
{
    use RenderExceptionTrait;

    protected $statusCode;

    public function __construct(string $message = 'خطا در احراز هویت', int $statusCode = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
    }
}
