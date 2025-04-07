<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class EmailVerifiedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        /** @var User $user */
        $user = $event->user;

        ReservedTicket::where('email', $user->email)->where('user_id', null)->each(
            function (ReservedTicket $reservedTicket) use ($user) {
                $reservedTicket->user_id = $user->id;
                $reservedTicket->save();

                // If the reserved ticket type has a price of 0, automatically create a purchased ticket when possible
                if ($reservedTicket->ticketType->price === 0 && $reservedTicket->can_be_purchased) {
                    $purchasedTicket = new PurchasedTicket;
                    $purchasedTicket->ticket_type_id = $reservedTicket->ticket_type_id;
                    $purchasedTicket->user_id = $user->id;
                    $purchasedTicket->reserved_ticket_id = $reservedTicket->id;
                    $purchasedTicket->save();
                }
            }
        );
    }
}
