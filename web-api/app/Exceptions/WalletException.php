<?php

namespace App\Exceptions;

use Exception;

class WalletException extends Exception
{
    use RenderExceptionTrait;

    public function __construct(string $message = 'خطا در عملیات کیف پول', int $statusCode = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
    }
}
