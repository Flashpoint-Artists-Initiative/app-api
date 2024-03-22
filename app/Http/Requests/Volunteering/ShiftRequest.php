<?php

declare(strict_types=1);

namespace App\Http\Requests\Volunteering;

use Orion\Http\Requests\Request;

class ShiftRequest extends Request
{
    public function commonRules(): array
    {
        return [
            'start_offset' => ['integer', 'gte:0'],
            'multiplier' => ['nullable', 'integer', 'gte:1'],
            'length' => ['nullable', 'integer', 'gt:0'],
            'num_spots' => ['nullable', 'integer', 'gt:0'],
        ];
    }

    public function storeRules(): array
    {
        return [
            'start_offset' => ['integer', 'required', 'gte:0'],
        ];
    }
}
