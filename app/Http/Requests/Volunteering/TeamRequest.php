<?php

declare(strict_types=1);

namespace App\Http\Requests\Volunteering;

use Orion\Http\Requests\Request;

class TeamRequest extends Request
{
    public function storeRules(): array
    {
        return [
            'name' => ['string', 'required'],
            'description' => ['string', 'required'],
            'email' => ['nullable', 'string', 'email'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function updateRules(): array
    {
        return [
            'name' => ['string'],
            'description' => ['string'],
            'email' => ['string', 'email'],
            'active' => ['boolean'],
        ];
    }
}
