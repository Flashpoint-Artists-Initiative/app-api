<?php

declare(strict_types=1);

namespace App\Policies;

class OrderPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'orders';
}
