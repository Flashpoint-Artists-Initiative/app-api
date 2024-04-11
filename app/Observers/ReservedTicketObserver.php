<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\User;

class ReservedTicketObserver
{
    public function saving(ReservedTicket $reservedTicket): void
    {
        // Check submitted email for a matching user, and if found assign to user_id
        if ($reservedTicket->isDirty('email')) {
            $user_id = User::where('email', $reservedTicket->email)->value('id');

            if ($user_id) {
                $reservedTicket->user_id = $user_id;
            }
        }
    }

    /**
     * Handle the ReservedTicket "updated" event.
     */
    public function saved(ReservedTicket $reservedTicket): void
    {
        // If the reserved ticket type has a price of 0, automatically create a purchased ticket when possible
        if ($reservedTicket->user_id
        && $reservedTicket->ticketType->price === 0
        && $reservedTicket->can_be_purchased
        ) {
            $purchasedTicket = new PurchasedTicket();
            $purchasedTicket->ticket_type_id = $reservedTicket->ticket_type_id;
            $purchasedTicket->user_id = $reservedTicket->user_id;
            $purchasedTicket->reserved_ticket_id = $reservedTicket->id;
            $purchasedTicket->save();
        }
    }

    /**
     * Handle the ReservedTicket "deleted" event.
     */
    public function updating(ReservedTicket $reservedTicket): bool
    {
        if ($reservedTicket->is_purchased) {
            return false;
        }

        return true;
    }

    /**
     * Handle the ReservedTicket "restored" event.
     */
    public function deleting(ReservedTicket $reservedTicket): bool
    {
        if ($reservedTicket->is_purchased) {
            return false;
        }

        return true;
    }
}
