<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Volunteering;

use App\Http\Controllers\OrionRelationsController;
use App\Models\Volunteering\ShiftType;

class ShiftRequirementsController extends OrionRelationsController
{
    protected $model = ShiftType::class;

    protected $relation = 'requirements';

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
