<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request;

class EventsController extends Controller
{
    protected $model = Event::class;

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'search']);

        parent::__construct();
    }

    public function includes(): array
    {
        return ['purchasedTickets', 'reservedTickets', 'ticketTypes'];
    }

    public function aggregates(): array
    {
        return ['purchasedTickets', 'reservedTickets', 'ticketTypes', 'ticketTypes.*'];
    }

    public function filterableBy(): array
    {
        return ['active'];
    }

    /**
     * Builds Eloquent query for fetching entities in index method.
     */
    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);

        // Hide non-active events for users without specific permission to view them
        if (! auth()->user()?->can('events.viewPending')) {
            $query->where('active', true);
        }

        // Hide soft-deleted events for users without specific permission to view them
        if (! auth()->user()?->can('events.viewDeleted')) {
            dd(1);
            // @phpstan-ignore-next-line
            $query->withoutTrashed();
        }

        return $query;
    }

    protected function buildShowFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildShowFetchQuery($request, $requestedRelations);

        // Hide soft-deleted events for users without specific permission to view them
        if (! auth()->user()?->can('events.viewDeleted')) {
            // @phpstan-ignore-next-line
            $query->withoutTrashed();
        }

        return $query;
    }
}
