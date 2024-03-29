<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Events;

use App\Http\Controllers\OrionRelationsController;
use App\Http\Requests\WaiverCompleteRequest;
use App\Models\Event;
use App\Models\Ticketing\CompletedWaiver;
use App\Models\Ticketing\Waiver;

class WaiversController extends OrionRelationsController
{
    protected $model = Event::class;

    protected $relation = 'waivers';

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'search']);

        parent::__construct();
    }

    public function completeAction(Event $event, Waiver $waiver, WaiverCompleteRequest $request)
    {
        $this->authorize('complete', [$waiver]);
        $completedWaiver = CompletedWaiver::create([
            'waiver_id' => $waiver->id,
            'user_id' => auth()->user()->id,
            'form_data' => $request->validated('form_data'),
        ]);

        return $completedWaiver;
    }
}
