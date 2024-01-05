<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class EventRequest extends Request
{
    public function commonRules(): array
    {
        return [
            'name' => 'string',
            'start_date' => 'date',
            'end_date' => 'date',
            'contact_email' => 'email',
            'active' => 'boolean',
            'location' => 'string',
        ];
    }

    public function storeRules(): array
    {
        return [
            'name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'contact_email' => 'required|email',
        ];
    }
}
