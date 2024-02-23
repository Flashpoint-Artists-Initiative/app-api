<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Volunteering;

use App\Http\Controllers\OrionRelationsController;
use App\Http\Requests\Volunteering\ShiftRequest;
use App\Models\Volunteering\ShiftType;

class ShiftsController extends OrionRelationsController
{
    protected $model = ShiftType::class;

    protected $relation = 'shifts';

    // protected $request = ShiftRequest::class;

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'search']);

        parent::__construct();
    }

    public function signupAction()
    {

    }

    public function cancelAction()
    {

    }
}
