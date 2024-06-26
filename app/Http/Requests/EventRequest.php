<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class EventRequest extends Request
{
    /**
     * @return array<string, string[]>
     */
    public function commonRules(): array
    {
        return [
            'name' => ['string'],
            'start_date' => ['date'],
            'end_date' => ['date'],
            'contact_email' => ['email'],
            'active' => ['boolean', 'nullable'],
            'location' => ['string', 'nullable'],
        ];
    }

    /**
     * @return array<string, string[]>
     */
    public function storeRules(): array
    {
        return [
            'name' => ['required'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'contact_email' => ['required', 'email'],
        ];
    }
}
