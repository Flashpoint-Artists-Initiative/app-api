<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Volunteering;

use App\Http\Controllers\OrionRelationsController;
use App\Models\Volunteering\ShiftType;
use App\Policies\Volunteering\ShiftRequirementPolicy;
use App\Policies\Volunteering\ShiftTypePolicy;

class ShiftRequirementsController extends OrionRelationsController
{
    protected $model = ShiftType::class;

    protected $relation = 'requirements';

    protected $policy = ShiftRequirementPolicy::class;

    protected $parentPolicy = ShiftTypePolicy::class;

    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show', 'search']);

        parent::__construct();
    }
}
