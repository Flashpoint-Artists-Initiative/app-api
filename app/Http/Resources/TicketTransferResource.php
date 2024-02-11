<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketTransferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $array = parent::toArray($request);

        return array_merge($array, [
            'purchased_tickets' => $this->whenLoaded('purchasedTickets'),
            'reserved_tickets' => ReservedTicketResource::collection($this->whenLoaded('reservedTickets')),
        ]);
    }
}
