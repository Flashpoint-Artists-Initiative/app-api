<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Orion\Http\Controllers\Controller as BaseController;

abstract class OrionRelationsController extends BaseController
{
    public function limit(): int
    {
        return config('orion.default_pagination_limit', 50);
    }

    public function maxLimit(): ?int
    {
        return config('orion.default_pagination_max_limit', 200);
    }
}
