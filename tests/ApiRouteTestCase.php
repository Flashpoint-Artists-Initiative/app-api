<?php

declare(strict_types=1);

namespace Tests;

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

        if (! empty($this->routeName)) {
            $this->endpoint = route($this->routeName, $this->routeParams, false);
        }
    }
}
