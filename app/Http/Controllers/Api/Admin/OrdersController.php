<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\OrionController;
use App\Models\Ticketing\Order;

class OrdersController extends OrionController
{
    protected $model = Order::class;

    /**
     * @return string[]
     */
    public function filterableBy(): array
    {
        return [
            'user_id',
            'event_id',
            'cart_id',
        ];
    }

    /**
     * @return string[]
     */
    public function sortableBy(): array
    {
        return [
            'created_at',
        ];
    }
}
