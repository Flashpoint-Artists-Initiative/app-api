<?php

declare(strict_types=1);

namespace App\Providers;

use App\Stripe\StripeGateway;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class StripeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->registerStripe();
    }

    public function provides()
    {
        return [
            'stripe',
        ];
    }

    protected function registerStripe()
    {
        $this->app->singleton('stripe', function ($app) {
            $config = $app['config']->get('services.stripe');

            $secret = $config['secret'] ?? null;

            $client = new StripeClient($secret);

            return new StripeGateway($client);
        });
    }
}
