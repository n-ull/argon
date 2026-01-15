<?php

namespace Domain\Ticketing\Enums;

enum TicketStatus: string
{
    case ACTIVE = 'active';
    case USED = 'used';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}
