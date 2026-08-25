# Auth-Kit OTP

One-time password (OTP) plugin for
[Auth-Kit](https://github.com/gusmanwidodo/auth-kit). A standalone Composer
package that plugs into the Auth-Kit core to add OTP issue/verify endpoints, a
code store, and expiry enforcement via the core hook pipeline.

[![Tests](https://github.com/gusmanwidodo/auth-kit-otp/actions/workflows/tests.yml/badge.svg)](https://github.com/gusmanwidodo/auth-kit-otp/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## What it demonstrates

This package is the reference example of the Auth-Kit plugin model. It uses the
**full plugin surface** as an independent package that only depends on the core:

- `HasSchema` — ships the `auth_kit_otp_codes` migration
- `HasRoutes` — adds `POST /auth-kit/otp/issue` and `POST /auth-kit/otp/verify`
- `HasHooks` — a `before:otp.verify` hook that rejects expired codes

## Requirements

- PHP `^8.3`
- `gusmanwidodo/auth-kit` `^0.1`
- Laravel 11 or 12

## Installation

```bash
composer require gusmanwidodo/auth-kit-otp
```

Both the core and this plugin are auto-discovered. Run migrations to create the
OTP table:

```bash
php artisan migrate
```

Optionally publish the config:

```bash
php artisan vendor:publish --tag=auth-kit-otp-config
```

## Endpoints

| Method | URI                     | Body                        | Purpose |
|--------|-------------------------|-----------------------------|---------|
| POST   | `/auth-kit/otp/issue`   | `{ identifier }`            | Generate + store a hashed code |
| POST   | `/auth-kit/otp/verify`  | `{ identifier, code }`      | Verify, running the hook pipeline |

> In production, deliver the code via mail/SMS. The `code` is only returned in
> the `issue` response while running tests.

## Config

`config/auth-kit-otp.php`:

```php
'ttl'    => 300, // seconds a code stays valid
'length' => 6,   // number of digits
```

## Developing against a local core

When both repos are checked out side by side, point Composer at the local core
before installing:

```bash
composer config repositories.auth-kit path ../auth-kit
composer require gusmanwidodo/auth-kit:@dev
composer install
composer test
```

## License

MIT © Gusman Widodo. See [LICENSE](LICENSE).
