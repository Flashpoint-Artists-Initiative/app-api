<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class CompletedWaiverRequest extends Request
{
    /**
     * @return array<string, string[]>
     */
    public function storeRules(): array
    {
        return [
            'waiver_id' => ['required', 'integer', 'exists:waivers,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'form_data' => ['json', 'nullable'],
            'paper_completion' => ['boolean', 'nullable'],
        ];
    }
}
