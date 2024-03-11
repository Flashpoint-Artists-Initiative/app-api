<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Volunteering;

use App\Http\Controllers\OrionController;
use App\Models\Volunteering\Requirement;
use App\Policies\Volunteering\ShiftRequirementPolicy;

class RequirementsController extends OrionController
{
    protected $model = Requirement::class;

    protected $policy = ShiftRequirementPolicy::class;
}
