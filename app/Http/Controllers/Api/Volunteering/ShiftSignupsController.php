<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Volunteering;

use App\Http\Controllers\OrionRelationsController;
use App\Models\Volunteering\Shift;

class ShiftSignupsController extends OrionRelationsController
{
    protected $model = Shift::class;

    protected $relation = 'volunteers';

    public function __construct()
    {
        parent::__construct();
    }

    public function signupAction()
    {

    }

    public function cancelAction()
    {

    }
}
