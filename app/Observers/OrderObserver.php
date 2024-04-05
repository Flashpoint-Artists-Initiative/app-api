<?php

declare(strict_types=1);

namespace App\Observers;

use App\Mail\OrderCompletedMail;
use App\Models\Ticketing\Order;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        Mail::to($order->user)->sendNow(new OrderCompletedMail($order));
    }
}
