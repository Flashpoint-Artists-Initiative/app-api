<x-filament-panels::page>
    <div wire:loading wire:target.except="checkout">
        <x-filament::loading-indicator class="h-5 w-5" />
    </div>
    @if ($cart)
    <x-notification-banner color="info" wire:loading.remove wire:target.except="checkout">
            You already have an existing cart with {{  $cart->quantity }} {{ Str::of('ticket')->plural($cart->quantity) }}!  It will expire {{ $cart->expiration_date->diffForHumans() }}. {{ $this->checkoutAction }}
    </x-notification-banner>
    @endif
    @if ($hasPurchasedTickets)
    <x-notification-banner color="success" wire:loading.remove wire:target.except="checkout">
            You've already got a ticket for this event! You can buy more, but every attendee will need to register their own account here. {{ $this->ticketInfoAction }}
    </x-notification-banner>
    @endif

    <div wire:loading.remove wire:target.except="checkout">
        <x-filament-panels::form wire:submit="checkout" onkeydown="return event.key != 'Enter';" class="purchase-tickets-form">
            {{ $this->form }}
        </x-filament-panels::form>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
