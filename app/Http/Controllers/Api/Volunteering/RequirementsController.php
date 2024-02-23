<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Volunteering;

use App\Http\Controllers\OrionController;
use App\Models\Volunteering\Requirement;

class RequirementsController extends OrionController
{
    protected $model = Requirement::class;
}
