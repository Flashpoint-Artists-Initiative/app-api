<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Ticketing\TicketTransfer;

class TicketTransferObserver
{
    public function deleting(TicketTransfer $ticketTransfer): bool
    {
        return ! $ticketTransfer->completed;
    }

    public function updating(TicketTransfer $ticketTransfer): bool
    {
        return false;
    }

    public function deleted(TicketTransfer $ticketTransfer): void
    {
        $ticketTransfer->purchasedTickets()->detach();
        $ticketTransfer->reservedTickets()->detach();
    }
}
