<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Volunteering;

use App\Http\Controllers\OrionRelationsController;
use App\Http\Requests\Volunteering\TeamRequest;
use App\Models\Event;

class TeamsController extends OrionRelationsController
{
    protected $model = Event::class;

    protected $relation = 'teams';

    // protected $request = TeamRequest::class;

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'search']);

        parent::__construct();
    }
}
