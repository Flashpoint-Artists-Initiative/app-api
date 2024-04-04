<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\OrionController;
use App\Policies\AuditPolicy;
use OwenIt\Auditing\Models\Audit;

class AuditController extends OrionController
{
    protected $model = Audit::class;

    protected $policy = AuditPolicy::class;

    public function includes(): array
    {
        return [
            'user',
            'auditable',
        ];
    }

    public function filterableBy(): array
    {
        return [
            'id',
            'user_id',
            'event',
            'auditable_type',
            'auditable_id',
            'url',
            'ip_address',
            'user_agent',
            'tags',
            'created_at',
        ];
    }

    public function sortableBy(): array
    {
        return [
            'id',
            'user_id',
            'event',
            'auditable_type',
            'auditable_id',
            'url',
            'ip_address',
            'user_agent',
            'tags',
            'created_at',
        ];
    }

    public function searchableBy(): array
    {
        return [
            'id',
            'user_id',
            'event',
            'auditable_type',
            'auditable_id',
            'url',
            'ip_address',
            'user_agent',
            'tags',
            'created_at',
        ];
    }
}
