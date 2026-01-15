<?php

namespace Domain\Ticketing\Enums;

enum TicketType: string
{
    case STATIC = 'static';
    case DYNAMIC = 'dynamic';

}
