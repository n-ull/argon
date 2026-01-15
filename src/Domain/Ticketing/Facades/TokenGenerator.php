<?php

namespace Domain\Ticketing\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string generate(\Domain\Ticketing\Enums\TicketType $type)
 * 
 * @see \Domain\Ticketing\Support\TokenGenerator
 */
class TokenGenerator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Domain\Ticketing\Support\TokenGenerator::class;
    }
}
