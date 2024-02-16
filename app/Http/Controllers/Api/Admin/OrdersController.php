<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\OrionController;
use App\Models\Ticketing\Order;

class OrdersController extends OrionController
{
    protected $model = Order::class;
}
