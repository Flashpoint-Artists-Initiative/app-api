<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Volunteering\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Team
 */
class TeamResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'email' => $this->email,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'event_id' => $this->event_id,
            'total_num_spots' => $this->total_num_spots,
            'volunteers_count' => $this->volunteers_count,
            'percent_filled' => $this->percent_filled,
        ];
    }
}
