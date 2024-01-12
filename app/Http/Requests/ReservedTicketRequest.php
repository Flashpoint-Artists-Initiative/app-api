<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class ReservedTicketRequest extends Request
{
    public function commonRules(): array
    {
        return [
            'email' => 'email',
            'expiration_date' => 'date|nullable',
            'name' => 'string|nullable',
            'note' => 'string|nullable',
        ];
    }

    public function storeRules(): array
    {
        return [
            'email' => 'email|required',
        ];
    }
}
