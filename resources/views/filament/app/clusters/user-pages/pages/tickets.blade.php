<x-filament-panels::page>
    @if ($hasMultipleTickets)
        <x-notification-banner color="info">
            You have multiple tickets for this event! Every person attending the event must have their own POTION account and ticket. {{ $this->ticketInfoAction }}
        </x-notification-banner>
    @endif
    {{  $this->ticketsInfolist }}
</x-filament-panels::page>
