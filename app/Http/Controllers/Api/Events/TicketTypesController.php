<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Orion\Http\Controllers\RelationController;
use Orion\Http\Requests\Request;

class TicketTypesController extends RelationController
{
    protected $model = Event::class;

    protected $relation = 'ticketTypes';

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'search']);

        parent::__construct();
    }

    public function includes(): array
    {
        return ['event', 'purchasedTickets', 'reservedTickets'];
    }

    protected function buildIndexFetchQuery(Request $request, Model $event, array $requestedRelations): Relation
    {
        $relation = parent::buildIndexFetchQuery($request, $event, $requestedRelations);
        /** @var ?User $user */
        $user = auth()->user();

        // Hide non-active events for users without specific permission to view them
        if (! $user?->can('ticketTypes.viewPending')) {
            $relation->getQuery()->where('active', true);
        }

        // Hide soft-deleted events for users without specific permission to view them
        if (! $user?->can('ticketTypes.viewDeleted')) {
            // @phpstan-ignore-next-line
            $relation->getQuery()->withoutTrashed();
        }

        return $relation;
    }

    protected function buildShowFetchQuery(Request $request, Model $event, array $requestedRelations): Relation
    {
        $relation = parent::buildShowFetchQuery($request, $event, $requestedRelations);
        /** @var ?User $user */
        $user = auth()->user();

        // Hide soft-deleted events for users without specific permission to view them
        if (! $user?->can('events.viewDeleted')) {
            // @phpstan-ignore-next-line
            $relation->getQuery()->withoutTrashed();
        }

        return $relation;
    }
}
