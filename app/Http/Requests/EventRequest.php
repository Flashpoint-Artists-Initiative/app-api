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
            'start_date' => ['date', 'before_or_equal:end_date'],
            'end_date' => ['date', 'after_or_equal:start_date'],
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
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
