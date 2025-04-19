<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\Ticketing\TicketType;

class MetricsService
{
    /**
     * @return array<string, array<int|string, mixed>>
     */
    public function getTicketQuantityData(int $eventId): array
    {
        $event = Event::where('id', $eventId)->with('ticketTypes')->firstOrFail();
        $ticketTypes = $event->ticketTypes->filter(fn (TicketType $t) => $t->active);

        $data = $ticketTypes->map(function (TicketType $type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'quantity' => $type->quantity,
                'remaining' => $type->remainingTicketCount,
                'purchased' => $type->purchased_tickets_count,
                'in_carts' => (int) $type->cartItemsQuantity,
                'unsold_reserved' => $type->unsold_reserved_tickets_count,
            ];
        });

        return [
            'total' => [
                'quantity' => $data->sum('quantity'),
                'remaining' => $data->sum('remaining'),
                'purchased' => $data->sum('purchased'),
                'in_carts' => $data->sum('in_carts'),
                'unsold_reserved' => $data->sum('unsold_reserved'),
            ],
            'individual' => array_values($data->toArray()),
        ];
    }
}
