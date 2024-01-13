<?php

namespace mehrab\eloquestic\Exceptions;

use Throwable;

class ToManyLargeException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $message='The array size should not exceed 10,000 elements. Please reduce the number of items in the array and try again';
        parent::__construct($message, $code, $previous);
    }
}
