<?php

declare(strict_types=1);

namespace App\Http\Requests\Checkout;

use App\Models\Ticketing\ReservedTicket;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutCreateReservedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->hasUser();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tickets' => ['required', 'array'],
            'tickets.*' => ['required', 'distinct', 'integer', 'exists:reserved_tickets,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        if ($validator->errors()->count() > 0) {
            return;
        }

        $this->reservedValidation($validator);
    }

    public function reservedValidation(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var User $user */
            $user = auth()->user();
            $user->load('reservedTickets.ticketType');

            // Get valid reserved tickets
            $reservedTickets = $user->reservedTickets->filter(function (ReservedTicket $value) {
                return $value->can_be_purchased;
            });

            // Validate that the ticket type event ids are all the same
            $eventIds = $reservedTickets->pluck('ticketType.event_id')->toArray();
            if (count(array_unique($eventIds)) > 1) {
                $validator->errors()->add('tickets', 'All ticket types must belong to the same event');
            }

            // Attach the event_id to the request for easy reference later
            if (count($eventIds) > 0) {
                $this->merge(['event_id' => $eventIds[0]]);
            }

            // Check each ticket type passed, make sure there are enough valid reserved tickets of the selected type
            foreach ($this->input('tickets') as $id) {
                $matchingTicket = $reservedTickets->find($id);
                if (is_null($matchingTicket)) {
                    $validator->errors()->add('tickets', 'Cannot find matching reserved ticket for this user.');
                }
            }
        });
    }
}
