<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use OwenIt\Auditing\Models\Audit;

/**
 * @mixin Audit
 *
 * @property int $user_id
 */
class AuditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<mixed> */
        $array = parent::toArray($request);
        $array['user_name'] = $this->user_id ? $this->user->display_name : new MissingValue;
        $array['modified'] = $this->getModified();

        unset($array['user'], $array['user_type'], $array['old_values'], $array['new_values']);

        return $array;
    }
}
