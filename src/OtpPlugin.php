<?php

declare(strict_types=1);

namespace Gusmanwidodo\AuthKitOtp;

use Gusmanwidodo\AuthKit\Contracts\AuthPlugin;
use Gusmanwidodo\AuthKit\Contracts\HasHooks;
use Gusmanwidodo\AuthKit\Contracts\HasRoutes;
use Gusmanwidodo\AuthKit\Contracts\HasSchema;
use Gusmanwidodo\AuthKit\HookContext;

/**
 * OTP plugin: adds one-time-password issue/verify endpoints, a code store
 * table, and a `before:otp.verify` hook that rejects expired codes.
 *
 * Demonstrates the full plugin surface (schema + routes + hooks) as a
 * standalone Composer package that only depends on the auth-kit core.
 */
class OtpPlugin implements AuthPlugin, HasSchema, HasRoutes, HasHooks
{
    /**
     * @param array{ttl?: int, length?: int} $options
     */
    public function __construct(
        private readonly array $options = [],
    ) {
    }

    public function id(): string
    {
        return 'otp';
    }

    public function boot(): void
    {
        // No container-time wiring needed for this plugin.
    }

    /** Time-to-live for a generated code, in seconds. */
    public function ttl(): int
    {
        return $this->options['ttl'] ?? 300;
    }

    /** Number of digits in a generated code. */
    public function length(): int
    {
        return $this->options['length'] ?? 6;
    }

    public function migrationPaths(): array
    {
        return [__DIR__ . '/../database/migrations'];
    }

    public function routePaths(): array
    {
        return [__DIR__ . '/../routes/otp.php'];
    }

    public function beforeHooks(): array
    {
        return [
            'otp.verify' => function (HookContext $context): void {
                $expiresAt = $context->get('expires_at');

                if ($expiresAt !== null && $expiresAt < now()->timestamp) {
                    $context->set('valid', false)->stop();
                }
            },
        ];
    }

    public function afterHooks(): array
    {
        return [];
    }
}
