<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Ticketing\ReservedTicket;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TicketTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purchased_tickets' => ['array'],
            'purchased_tickets.*' => ['distinct', 'integer', 'exists:reserved_tickets,id'],
            'reserved_tickets' => ['array'],
            'reserved_tickets.*' => ['distinct', 'integer', 'exists:purchased_tickets,id'],
            'email' => ['required', 'email', 'exists:users,email'],
        ];
    }

    public function messages()
    {
        return [
            'email' => 'Cannot find matching user email.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->purchased_tickets)) {
            $this->merge(['purchased_tickets' => [$this->purchased_tickets]]);
        }

        if (is_string($this->reserved_tickets)) {
            $this->merge(['reserved_tickets' => [$this->reserved_tickets]]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->purchased_tickets && ! $this->reserved_tickets) {
                return;
            }

            $transferUser = User::where('email', $this->email)->firstOrFail();

            /** @var User $user */
            $user = auth()->user();
            $user->load(['reservedTickets.ticketType', 'purchasedTickets.ticketType']);

            // Get valid reserved tickets
            $reservedTickets = $user->reservedTickets->filter(function (ReservedTicket $ticket) {
                return $ticket->can_be_purchased && ! $ticket->is_purchased;
            });

            // Check each ticket type passed, make sure there are enough valid reserved tickets of the selected type
            foreach ($this->input('reserved_tickets', []) as $key => $id) {
                $matchingTicket = $reservedTickets->find($id);
                if (is_null($matchingTicket)) {
                    $validator->errors()->add("reserved_tickets.$key", 'Cannot find valid reserved ticket for this user.');
                }

                if ($matchingTicket->user_id == $transferUser->id) {
                    $validator->errors()->add("reserved_tickets.$key", 'Reserved ticket already belongs to that user.');
                }
            }

            // Purchased tickets are simpler because they're all valid
            foreach ($this->input('purchased_tickets', []) as $key => $id) {
                $matchingTicket = $user->purchasedTickets->find($id);
                if (is_null($matchingTicket)) {
                    $validator->errors()->add("purchased_tickets.$key", 'Cannot find purchased ticket for this user.');
                }

                if ($matchingTicket->user_id == $transferUser->id) {
                    $validator->errors()->add("reserved_tickets.$key", 'Purchased ticket already belongs to that user.');
                }
            }
        });
    }
}
