<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Ticketing\PurchasedTicket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PurchasedTicket
 */
class PurchasedTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<mixed> $result */
        $result = parent::toArray($request);

        return array_merge($result, [
            'ticket_type' => $this->whenPivotLoaded('ticket_transfer_items', $this->ticketType),
            'has_active_transfer' => $this->has_active_transfer,
        ]);
    }
}
