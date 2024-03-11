<?php

declare(strict_types=1);

namespace App\Http\Requests\Volunteering;

use App\Models\User;
use App\Models\Volunteering\Shift;
use App\Services\VolunteerService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ShiftSignupRequest extends FormRequest
{
    public function __construct(protected VolunteerService $volunteerService)
    {

    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Shift $shift */
            $shift = $this->route('shift');

            // Prevent signup if shift is full
            if ($shift->volunteers_count >= $shift->length) {
                $validator->errors()->add('shift', 'This shift is full');
            }

            // Prevent double signups
            /** @var User $user */
            $user = auth()->user();

            if ($user->shifts()->where('id', $shift->id)->exists()) {
                $validator->errors()->add('shift', "You've already signed up for this shift");

                return;
            }

            // Prevent overlapping shifts
            if ($conflict = $this->volunteerService->userHasOverlappingShift($shift)) {
                $message = sprintf("This shift overlaps with an existing shift you've signed up for: %s - %s at %s",
                    $conflict->shiftType->team->name,
                    $conflict->title,
                    $conflict->start_datetime);

                $validator->errors()->add('shift', $message);
            }
        });
    }
}
