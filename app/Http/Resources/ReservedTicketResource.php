<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Ticketing\ReservedTicket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReservedTicket
 */
class ReservedTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $result = parent::toArray($request);

        return array_merge($result, [
            'is_purchased' => $this->is_purchased,
        ]);
    }
}
