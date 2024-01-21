<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class DateFilterException extends HttpException
{
    public function __construct($message = null, ?\Throwable $previous = null, array $headers = [], $code = 0)
    {
        parent::__construct(400, $message, $previous, $headers, $code);
    }

    public static function incorrectDateFormatSupplied(): static
    {
        return new static(
            'Supplied date is using incorrect date format.'
        );
    }
}
