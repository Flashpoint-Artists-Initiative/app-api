<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id' => $this->id,
            'legal_name' => $this->legal_name,
            'preferred_name' => $this->preferred_name,
            /** @var string $display_name Calculated Fields */
            'display_name' => $this->display_name,
            'birthday' => $this->birthday,
            'email' => $this->email,
            'roles' => $this->whenLoaded('roles'),
        ];
    }
}
