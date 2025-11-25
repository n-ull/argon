<?php

namespace Domain\Ordering\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case UNKNOWN = '';
}
