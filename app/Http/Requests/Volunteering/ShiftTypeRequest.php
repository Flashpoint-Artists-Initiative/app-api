<?php

declare(strict_types=1);

namespace App\Http\Requests\Volunteering;

use Orion\Http\Requests\Request;

class ShiftTypeRequest extends Request
{
    public function storeRules(): array
    {
        return [
            'title' => ['string', 'required'],
            'description' => ['string', 'required'],
            'length' => ['integer', 'required', 'gt:0'],
            'num_spots' => ['integer', 'required', 'gt:0'],
        ];
    }

    public function updateRules(): array
    {
        return [
            'title' => ['string'],
            'description' => ['string'],
            'length' => ['integer', 'gt:0'],
            'num_spots' => ['integer', 'gt:0'],
        ];
    }
}
