<?php

namespace Domain\EventManagement\Enums;

enum CalculationType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
}
