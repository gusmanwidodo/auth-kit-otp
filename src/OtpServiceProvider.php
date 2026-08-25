<?php

declare(strict_types=1);

namespace Gusmanwidodo\AuthKitOtp;

use Gusmanwidodo\AuthKit\AuthManager;
use Illuminate\Support\ServiceProvider;

/**
 * Self-registers the OTP plugin into the Auth-Kit core registry.
 *
 * This provider is auto-discovered by Laravel (see composer.json extra.laravel),
 * so installing the package is all a consuming app needs to do.
 */
class OtpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/auth-kit-otp.php', 'auth-kit-otp');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/auth-kit-otp.php' => $this->app->configPath('auth-kit-otp.php'),
        ], 'auth-kit-otp-config');

        // Self-register into the core registry. The core provider collects
        // routes/migrations inside an app->booted() callback (after every
        // provider's boot() has run), so provider ordering does not matter here.
        $manager = $this->app->make(AuthManager::class);
        $registry = $manager->registry();

        $options = (array) config('auth-kit-otp', []);

        if (! $registry->has('otp')) {
            $registry->register(new OtpPlugin($options));
        }
    }
}
