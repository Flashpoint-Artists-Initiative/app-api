<?php

declare(strict_types=1);

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

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
