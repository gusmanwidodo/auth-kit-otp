<?php

declare(strict_types=1);

namespace Gusmanwidodo\AuthKitOtp\Tests;

use Gusmanwidodo\AuthKit\AuthKitServiceProvider;
use Gusmanwidodo\AuthKitOtp\OtpServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        // Core first, then the plugin package — but the core collects routes
        // in an app->booted() callback, so order should not actually matter.
        return [
            AuthKitServiceProvider::class,
            OtpServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
