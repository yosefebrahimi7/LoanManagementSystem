<?php

namespace App\Exceptions;

use Exception;

class LoanException extends Exception
{
    use RenderExceptionTrait;

    public function __construct(string $message = 'خطا در عملیات وام', int $statusCode = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
    }

    public static function forbidden(string $message = 'شما به این عملیات دسترسی ندارید'): self
    {
        return new self($message, 403);
    }

    public static function badRequest(string $message = 'درخواست نامعتبر'): self
    {
        return new self($message, 400);
    }

    public static function notFound(string $message = 'وام یافت نشد'): self
    {
        return new self($message, 404);
    }
}
