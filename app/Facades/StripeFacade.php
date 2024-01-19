<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class StripeFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'stripe';
    }
}
