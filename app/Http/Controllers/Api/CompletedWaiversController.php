<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\OrionController;
use App\Models\Ticketing\CompletedWaiver;

class CompletedWaiversController extends OrionController
{
    protected $model = CompletedWaiver::class;
}
