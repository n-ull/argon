<?php

namespace Domain\Ordering\Exceptions;

use Exception;

class OrderAlreadyCompletedException extends Exception
{
    public function __construct(string $orderReferenceId, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Order $orderReferenceId is already completed.", $code, $previous);
    }
}
