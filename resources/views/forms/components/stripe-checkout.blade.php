<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
@php
    $checkoutSecret = $getCheckoutSecret();
    $stripeKey = config('services.stripe.api_key');
@endphp

{{-- There's probably a better way to do this javascript stuff in an external file --}}
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
            return '{{ $checkoutSecret }}';
        };

        const checkout = await stripe.initEmbeddedCheckout({
            fetchClientSecret,
        });

        // Mount Checkout
        checkout.mount('#stripe-checkout');
    }
</script>
@endscript

<div id="stripe-checkout">
</div>

</x-dynamic-component>