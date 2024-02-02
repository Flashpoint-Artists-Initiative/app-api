<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Events;

use App\Http\Controllers\OrionRelationsController;
use App\Models\Event;

class WaiversController extends OrionRelationsController
{
    protected $model = Event::class;

    protected $relation = 'waivers';
}
