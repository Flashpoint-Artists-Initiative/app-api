<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class ApiRouteTestCase extends TestCase
{
    use RefreshDatabase;

    public string $routeName;

    public string $endpoint;

    public function setUp(): void
    {
        parent::setUp();

        if (! empty($this->routeName)) {
            $this->endpoint = route($this->routeName, [], false);
        }
    }
}
