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
            'length' => ['integer', 'gt:0'],
            'num_spots' => ['integer', 'gt:0'],
        ];
    }

    public function storeRules(): array
    {
        return [
            'start_offset' => ['integer', 'required', 'gte:0'],
        ];
    }
}
