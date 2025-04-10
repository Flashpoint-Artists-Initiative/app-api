You're all set for {{$order->event->name}}!

Log in to your POTION profile to view your tickets.

{{  App\Filament\App\Clusters\UserPages\Pages\Tickets::getUrl(); }}

Please note: Tickets this year are attached to your account. If you've bought multiple tickets, every person in your group needs their own account to attend the event.

Tickets can be transferred by going to the 'Ticket Transfers' section of your user profile.

{{  App\Filament\App\Clusters\UserPages\Pages\TicketTransfers::getUrl(); }}

-----------------------------------------------------------------
Your Order #{{$order->id}}

@foreach ($order->ticket_data as $item)
{{$order->ticketTypes()->find($item['ticket_type_id'])->name}}
Quantity: {{$item['quantity']}}
Price: {{$order->ticketTypes()->find($item['ticket_type_id'])->price}}
Total: {{$order->ticketTypes()->find($item['ticket_type_id'])->price * $item['quantity']}}

@endforeach

Subtotal: ${{number_format($order->amount_subtotal / 100, 2)}}
Sales Tax: ${{number_format($order->amount_tax / 100, 2)}}
Stripe Fee: ${{number_format($order->amount_fees / 100, 2)}}

Total: ${{number_format($order->amount_total / 100, 2)}}

If you have any questions, please reply to this email or contact us at ticketing@alchemyburn.com