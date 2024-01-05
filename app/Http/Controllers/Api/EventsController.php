<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\OrionController;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Orion\Http\Requests\Request;

class EventsController extends OrionController
{
    protected $model = Event::class;

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'search']);

        parent::__construct();
    }

    public function includes(): array
    {
        return ['purchasedTickets', 'reservedTickets', 'ticketTypes', 'ticketTypes.*'];
    }

    public function aggregates(): array
    {
        return ['purchasedTickets', 'reservedTickets', 'ticketTypes', 'ticketTypes.*'];
    }

    public function filterableBy(): array
    {
        return ['active', 'start_date', 'end_date', 'name'];
    }

    public function sortableBy(): array
    {
        return ['start_date', 'end_date', 'active', 'name', 'location', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['start_date', 'end_date', 'name', 'description', 'location', 'created_at', 'updated_at'];
    }

    /**
     * Builds Eloquent query for fetching entities in index method.
     */
    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);

        /** @var ?User $user */
        $user = auth()->user();

        // Hide non-active events for users without specific permission to view them
        if (! $user || ! $user->can('events.viewPending')) {
            $query->where('active', true);
        }

        // Hide soft-deleted events for users without specific permission to view them
        if (! $user || ! $user->can('events.viewDeleted')) {
            // @phpstan-ignore-next-line
            $query->withoutTrashed();
        }

        return $query;
    }

    protected function buildShowFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildShowFetchQuery($request, $requestedRelations);

        /** @var ?User $user */
        $user = auth()->user();

        // Hide soft-deleted events for users without specific permission to view them
        if (! $user || ! $user->can('events.viewDeleted')) {
            // @phpstan-ignore-next-line
            $query->withoutTrashed();
        }

        return $query;
    }
}
