<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Controllers\Api\MeController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (! is_null($this->include)) {
            $this->replace(['include' => explode(',', $this->input('include', ''))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'include' => ['array'],
            'include.*' => [Rule::in(MeController::includes())],
        ];
    }
}
