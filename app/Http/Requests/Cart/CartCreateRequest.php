<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\Models\Ticketing\TicketType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CartCreateRequest extends FormRequest
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
            'tickets.*.quantity' => 'required|integer|max:' . config('app.cart_max_quantity'),
        ];
    }

    public function withValidator(Validator $validator)
    {
        if ($validator->errors()->count() > 0) {
            return;
        }

        $validator->after(function ($validator) {
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
}
