<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
@php
    $checkoutSecret = $getCheckoutSecret();
    $stripeKey = config('services.stripe.api_key');
@endphp

@assets
<script src="https://js.stripe.com/v3/"></script>
@endassets


<div x-load-js="[
    {{-- @js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('stripe-js')),
    @js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('stripe-checkout')), --}}
]">
    <div id="stripe-checkout">
    </div>
    
</div>
</x-dynamic-component>