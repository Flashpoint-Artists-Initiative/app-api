<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class ApiRouteTestCase extends TestCase
{
    use RefreshDatabase;

    public string $routeName;

    public array $routeParams = [];

    public string $endpoint;

    public function setUp(): void
    {
        parent::setUp();

        $this->buildEndpoint();
    }

    public function buildEndpoint(?string $name = null, ?array $params = null): void
    {
        if (empty($name)) {
            $name = $this->routeName;
        }

        if (empty($params)) {
            $params = $this->routeParams;
        }

        if (! empty($name)) {
            $this->endpoint = route($name, $params, false);
        }
    }

    /**
     * @param  \App\Models\User  $user
     */
    public function actingAs(Authenticatable $user, $guard = null)
    {
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard */
        $auth = auth();
        $token = $auth->login($user);

        return parent::actingAs($user, $guard)->withToken($token);
    }
}
