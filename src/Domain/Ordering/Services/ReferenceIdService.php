<?php

namespace Domain\Ordering\Services;

class ReferenceIdService
{
    public static function create(string $id): string
    {
        $environment = app()->environment();

        if ($environment === 'local' || $environment === 'testing') {
            return "TEST-{$id}";
        }

        return "ORD-{$id}";
    }
}
