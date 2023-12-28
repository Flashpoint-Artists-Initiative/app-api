<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailUpdated extends Registered
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public $user,
    ) {
    }
}
