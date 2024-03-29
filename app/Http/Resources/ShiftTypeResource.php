<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Volunteering\ShiftType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShiftType
 */
class ShiftTypeResource extends JsonResource
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
            'team' => $this->team->name,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'team_id' => $this->team_id,
            'length' => $this->length,
            'num_spots' => $this->num_spots,
            'total_num_spots' => $this->total_num_spots,
            'volunteers_count' => $this->volunteers_count,
            'percent_filled' => $this->percent_filled,
            'requirements' => $this->whenLoaded('requirements'),
        ];
    }
}
