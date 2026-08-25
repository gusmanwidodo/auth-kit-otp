# Changelog

All notable changes to `auth-kit-otp` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial OTP plugin: `issue` / `verify` endpoints under `/auth-kit/otp`.
- `auth_kit_otp_codes` migration (hashed codes, unix expiry, consumed marker).
- `before:otp.verify` hook rejecting expired codes via the core pipeline.
- Configurable code TTL and length.
