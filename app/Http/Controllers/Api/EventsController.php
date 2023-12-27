<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Orion\Http\Controllers\Controller;

class EventsController extends Controller
{
    protected $model = Event::class;
}
