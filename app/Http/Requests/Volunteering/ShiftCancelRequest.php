<?php

declare(strict_types=1);

namespace App\Http\Requests\Volunteering;

use App\Models\User;
use App\Models\Volunteering\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ShiftCancelRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Shift $shift */
            $shift = $this->route('shift');

            // Must be signed up to cancel
            /** @var User $user */
            $user = auth()->user();
            if (! $user->shifts()->where('id', $shift->id)->exists()) {
                $validator->errors()->add('shift', 'You are not signed up for this shift.');
            }
        });
    }
}
