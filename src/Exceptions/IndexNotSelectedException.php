<?php

namespace mehrab\eloquestic\Exceptions;

use Throwable;

class IndexNotSelectedException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $message='elastic service exceptions : please set a valid index name with method setIndex() on your model.';
        parent::__construct($message, $code, $previous);
    }
}
