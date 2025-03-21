import {loadStripe} from '@stripe/stripe-js';

const stripePromise = loadStripe('{{ $stripeKey }}');

export default async function InitCheckout({ checkoutId }) {
    const stripe = await stripePromise;

    // Create a Checkout Session
    const fetchClientSecret = async () => {
        return `${checkoutId}`;
    };

    const checkout = await stripe.initEmbeddedCheckout({
        fetchClientSecret,
    });

    // Mount Checkout
    // checkout.mount('#stripe-checkout');
    // return checkout;
}