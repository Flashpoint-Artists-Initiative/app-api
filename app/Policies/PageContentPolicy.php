<?php

declare(strict_types=1);

namespace App\Policies;

class PageContentPolicy extends AbstractModelPolicy
{
    // Reuse event permissions
    protected string $prefix = 'event';
}
