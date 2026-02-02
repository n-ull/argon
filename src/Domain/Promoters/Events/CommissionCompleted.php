<?php

namespace Domain\Promoters\Events;

use Domain\Promoters\Models\Commission;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommissionCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Commission $commission)
    {
    }
}
