<?php

namespace Domain\Ordering\Services;

use Illuminate\Support\Str;

class ReferenceIdService
{
    public static function create(): string
    {
        return 'ORD-'.strtoupper(Str::random(12));
    }
}
