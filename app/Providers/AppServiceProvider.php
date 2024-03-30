<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\User;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

/**
 * @property Application $app
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var mixed[]
     */
    public $bindings = [
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerStripeClient();
    }

    /**
     * Bootstrap any application services.
     *
     * @codeCoverageIgnore
     */
    public function boot(): void
    {
        Scramble::extendOpenApi(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer', 'JWT')
            );
        });

        // Add localhost to the whitelisted stripe webhook ips when testing or local
        if ($this->app->isLocal() || $this->app->runningUnitTests()) {
            config(['services.stripe.webhook_ips.WEBHOOKS.100' => env('STRIPE_LOCAL_WEBHOOK_IP', '127.0.0.1')]);
        }

        Relation::enforceMorphMap([
            'purchasedTicket' => PurchasedTicket::class,
            'reservedTicket' => ReservedTicket::class,
            'user' => User::class,
        ]);
    }

    protected function registerStripeClient()
    {
        $this->app->singleton(StripeClient::class, function ($app) {
            $config = $app['config']->get('services.stripe');

            $secret = $config['secret'] ?? null;

            return new StripeClient($secret);
        });
    }
}
