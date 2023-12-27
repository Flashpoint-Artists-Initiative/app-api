<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class EventRequest extends Request
{
    public function commonRules(): array
    {
        return [
            'name' => 'required',
            'start_date' => 'date|required',
            'end_date' => 'date|required',
            'contact_email' => 'email|required',
            'active' => 'boolean',
        ];
    }
}
