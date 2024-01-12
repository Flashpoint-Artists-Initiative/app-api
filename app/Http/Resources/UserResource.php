<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Collection;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->relationLoaded('permissions')) {
            /** @var Collection $collection */
            $collection = $this->getAllPermissions();
            $permissions = $collection->map(fn ($p) => $p->name);
        }

        return [
            'id' => $this->id,
            'legal_name' => $this->legal_name,
            'preferred_name' => $this->preferred_name,
            'display_name' => $this->display_name, // Virtual attribute
            'birthday' => $this->birthday,
            'email' => $this->email,
            'email_verified' => $this->email_verified_at != null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at ?? new MissingValue(),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'permissions' => $permissions ?? new MissingValue(),
            'purchased_tickets' => $this->whenLoaded('purchasedTickets'),
            'reserved_tickets' => ReservedTicketResource::collection($this->whenLoaded('reservedTickets')),
        ];
    }
}
