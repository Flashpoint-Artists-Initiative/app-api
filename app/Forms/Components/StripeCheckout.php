<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class StripeCheckout extends Field
{
    protected string $view = 'forms.components.stripe-checkout';

    public string | \Closure | null $checkout_client_secret;

    public function checkoutSecret(string | \Closure | null $checkoutSecret): static
    {
        $this->checkout_client_secret = $checkoutSecret;

        return $this;
    }

    public function getCheckoutSecret(): ?string
    {
        return $this->evaluate($this->checkout_client_secret);
    }
}
