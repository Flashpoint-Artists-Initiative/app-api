<?php

declare(strict_types=1);

namespace App\Http\Requests\Checkout;

use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutCreateRequest extends FormRequest
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
            'tickets' => 'required|array',
            'tickets.*.id' => 'required|distinct|exists:ticket_types',
            'tickets.*.quantity' => 'required|integer',
            'reserved' => 'boolean',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        if ($validator->errors()->count() > 0) {
            return;
        }

        if ($this->input('reserved')) {
            $this->reservedValidation($validator);
        } else {
            $this->generalSaleValidation($validator);
        }
    }

    public function generalSaleValidation(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Make sure the total quantity < the configured max
            $quantities = $this->input('tickets.*.quantity');
            $sum = array_sum($quantities);
            if ($sum > (int) config('app.cart_max_quantity')) {
                $validator->errors()->add('quantity_sum', 'The total number of tickets in the cart cannot be more than ' . config('app.cart_max_quantity'));

                return;
            }

            // Validate that the ticket type event ids are all the same
            $ids = $this->input('tickets.*.id');
            $ticketTypes = TicketType::findMany($ids);
            $eventIds = $ticketTypes->pluck('event_id')->toArray();
            if (count(array_unique($eventIds)) > 1) {
                $validator->errors()->add('same_event', 'All ticket types must belong to the same event');
            }

            // Ensure each ticket type is available to purchase
            $input = $this->input('tickets');
            foreach ($input as $key => $values) {
                $ticketType = $ticketTypes->find($values['id']);
                if (! $ticketType?->hasAvailable($values['quantity'])) {
                    $validator->errors()->add("tickets.$key.available", 'This ticket type is sold out or unavailable for purchase');
                }
            }
        });
    }

    public function reservedValidation(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var User $user */
            $user = auth()->user();
            $user->load('reservedTickets.ticketType');

            // Get valid reserved tickets
            $reservedTickets = $user->reservedTickets->filter(function (ReservedTicket $value, int $key) {
                return $value->can_be_purchased;
            });

            // Check each ticket type passed, make sure there are enough valid reserved tickets of the selected type
            foreach ($this->input('tickets') as $key => $row) {
                $matchingTickets = $reservedTickets->where('ticket_type_id', $row['id']);
                if ($matchingTickets->count() < $row['quantity']) {
                    $validator->errors()->add(
                        "tickets.$key.quantity",
                        sprintf('You do not have enough valid reserved tickets of this type. %d selected, %d available.',
                            $row['quantity'],
                            $matchingTickets->count())
                    );
                }
            }
        });
    }
}
