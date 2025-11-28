<?php

namespace Domain\Ordering\Exceptions;

use Exception;

class OrderAlreadyPendingException extends Exception
{
    public function __construct(public int $orderId)
    {
        parent::__construct('You already have a pending order.');
    }
}
