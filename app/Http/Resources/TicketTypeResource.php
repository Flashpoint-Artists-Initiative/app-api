<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Ticketing\TicketType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TicketType
 */
class TicketTypeResource extends JsonResource
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
            'remaining_ticket_count' => $this->remaining_ticket_count,
            'cart_items_quantity' => (int) $this->cart_items_quantity,
        ]);
    }
}
