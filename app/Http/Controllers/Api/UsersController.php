<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\OrionController;
use App\Http\Resources\AuditResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class UsersController extends OrionController
{
    protected $model = User::class;

    protected $resource = UserResource::class;

    public function includes(): array
    {
        return [
            'roles',
            'permissions',
            'purchasedTickets',
            'purchasedTickets.ticketType',
            'purchasedTickets.ticketType.event',
            'reservedTickets',
            'reservedTickets.ticketType',
            'reservedTickets.ticketType.event',
        ];
    }

    public function filterableBy(): array
    {
        return ['legal_name', 'preferred_name', 'display_name', 'email', 'birthday', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at'];
    }

    public function sortableBy(): array
    {
        return ['legal_name', 'preferred_name', 'display_name', 'email', 'birthday', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at'];
    }

    public function searchableBy(): array
    {
        return ['legal_name', 'preferred_name', 'display_name', 'email', 'birthday'];
    }

    public function historyAction(Request $request, User $user)
    {
        $this->authorize('history', [$user]);

        /** @phpstan-ignore-next-line */
        $audits = Audit::where('user_id', $user->id)->with('user')->paginate();

        return AuditResource::collection($audits);
    }
}
