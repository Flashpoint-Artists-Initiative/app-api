<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompletedWaiverRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'form_data' => ['json', 'nullable'],
        ];
    }
}
