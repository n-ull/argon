<?php

namespace Domain\Promoters\Exceptions;

use Exception;

class ActiveInvitationAlreadyExists extends Exception
{
    public function __construct(string $message = 'An active invitation already exists for this email.', int $code = 422)
    {
        parent::__construct($message, $code);
    }

}
