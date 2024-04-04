<?php

declare(strict_types=1);

namespace App\Policies;

class AuditPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'audits';
}
