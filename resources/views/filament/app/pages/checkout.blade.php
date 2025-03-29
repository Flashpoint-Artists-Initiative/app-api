<x-filament-panels::page>
@php
    $stripeKey = config('services.stripe.api_key');
@endphp

@assets
<script src="https://js.stripe.com/v3/"></script>
@endassets

@script
<script>
    const stripe = Stripe('{{ $stripeKey }}');

    initialize();

    // Create a Checkout Session
    async function initialize() {
        const fetchClientSecret = async () => {
            return '{{ $this->checkoutSecret }}';
        };

        const onComplete = () => {
            checkout.destroy();
            {{-- window.location.replace('{{ route('filament.app.events.show', $this->event) }}'); --}}
            $wire.checkoutComplete = true;
        };

        const checkout = await stripe.initEmbeddedCheckout({
            fetchClientSecret,
            onComplete,
        });

        // Mount Checkout
        checkout.mount('#stripe-checkout');
    }
</script>
@endscript


<div wire:show="!checkoutComplete" wire:cloak>
    <x-notification-banner color="info" class="mb-2">
        Your cart will expire in {{ $this->cart->expiration_date->diffForHumans() }}.
    </x-notification-banner>
    <div id="stripe-checkout">
    </div>
    
</div>
<div wire:show="checkoutComplete" wire:cloak>
    Checkout Complete!
</div>
</x-filament-panels::page>
