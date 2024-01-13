<?php

namespace mehrab\eloquestic\Exceptions;

use Throwable;

class ModelSearchableFieldsNotFoundException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $message='elastic service exceptions : please set a valid array of elastic searchable fields $elasticSearchAbles.';
        parent::__construct($message, $code, $previous);
    }
}
