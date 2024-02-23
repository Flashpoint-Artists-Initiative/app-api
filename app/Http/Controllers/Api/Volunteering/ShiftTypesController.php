<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Volunteering;

use App\Http\Controllers\OrionRelationsController;
use App\Http\Requests\Volunteering\ShiftRequest;
use App\Models\Volunteering\Team;

class ShiftTypesController extends OrionRelationsController
{
    protected $model = Team::class;

    protected $relation = 'shiftTypes';

    // protected $request = ShiftRequest::class;

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'search']);

        parent::__construct();
    }
}
