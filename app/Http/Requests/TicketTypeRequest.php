<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class TicketTypeRequest extends Request
{
    public function commonRules(): array
    {
        return [
            'name' => 'string',
            'sale_start_date' => 'date',
            'sale_end_date' => 'date',
            'quantity' => 'integer|gte:0',
            'price' => 'integer|gte:0',
            'active' => 'boolean',
            'description' => 'string',
        ];
    }

    public function storeRules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|integer|gte:0',
        ];
    }
}
