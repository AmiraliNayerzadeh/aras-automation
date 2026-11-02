<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * RefreshDatabase wipes whatever database it's connected to, and a cached
     * config (bootstrap/cache/config.php) silently beats phpunit.xml. phpunit.xml
     * points the cache paths elsewhere; this stops the run outright if a test
     * ever boots against anything but the in-memory database anyway.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException("Refusing to run tests against [{$connection}:{$database}].");
        }

        return $app;
    }
}
