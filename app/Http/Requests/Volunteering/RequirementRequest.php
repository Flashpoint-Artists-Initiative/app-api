<?php

declare(strict_types=1);

namespace App\Http\Requests\Volunteering;

use Orion\Http\Requests\Request;

class RequirementRequest extends Request
{
    public function commonRules(): array
    {
        return [
            'name' => ['string'],
            'icon' => ['string'],
            'description' => ['string'],
        ];
    }

    public function storeRules(): array
    {
        return [
            'name' => ['string', 'required'],
            'icon' => ['string', 'required'],
            'description' => ['string', 'required'],
        ];
    }
}
