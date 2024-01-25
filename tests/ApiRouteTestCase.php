<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

abstract class ApiRouteTestCase extends TestCase
{
    use LazilyRefreshDatabase;

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

    public function addEndpointParams(array $params): void
    {
        $this->routeParams = array_merge($this->routeParams, $params);
        $this->buildEndpoint();
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
