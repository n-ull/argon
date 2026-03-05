<?php

namespace Domain\Ordering\Enums;

enum VoucherType: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';
}
