<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Volunteering;

use App\Http\Controllers\OrionRelationsController;
use App\Http\Requests\Volunteering\TeamRequest;
use App\Models\Event;
use App\Policies\Volunteering\TeamPolicy;

class TeamsController extends OrionRelationsController
{
    protected $model = Event::class;

    protected $relation = 'teams';

    protected $request = TeamRequest::class;

    protected $policy = TeamPolicy::class;

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'search']);

        parent::__construct();
    }
}
