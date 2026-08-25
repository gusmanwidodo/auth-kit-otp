<?php

declare(strict_types=1);

namespace Gusmanwidodo\AuthKitOtp\Http;

use Gusmanwidodo\AuthKit\AuthManager;
use Gusmanwidodo\AuthKitOtp\Models\OtpCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OtpController
{
    public function __construct(
        private readonly AuthManager $auth,
    ) {
    }

    /** Issue a fresh OTP code for the given identifier. */
    public function issue(Request $request): JsonResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $config = (array) config('auth-kit-otp', []);
        $length = (int) ($config['length'] ?? 6);
        $ttl = (int) ($config['ttl'] ?? 300);

        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
        $expiresAt = now()->timestamp + $ttl;

        OtpCode::create([
            'identifier' => $data['identifier'],
            'code_hash' => Hash::make($code),
            'expires_at' => $expiresAt,
        ]);

        // In production you would dispatch the code via mail/SMS, never return it.
        return response()->json([
            'issued' => true,
            'expires_at' => $expiresAt,
            'code' => app()->runningUnitTests() ? $code : null,
        ], 201);
    }

    /** Verify an OTP code, running the core hook pipeline first. */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string'],
        ]);

        $record = OtpCode::query()
            ->where('identifier', $data['identifier'])
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if ($record === null) {
            return response()->json(['valid' => false, 'reason' => 'not_found'], 422);
        }

        // Run the core before-hook pipeline. The OTP plugin's own hook rejects
        // expired codes; other plugins can hook the same event too.
        $context = $this->auth->runBefore('otp.verify', [
            'identifier' => $record->identifier,
            'expires_at' => $record->expires_at,
            'valid' => true,
        ]);

        if ($context->get('valid') === false) {
            return response()->json(['valid' => false, 'reason' => 'expired'], 422);
        }

        if (! Hash::check($data['code'], $record->code_hash)) {
            return response()->json(['valid' => false, 'reason' => 'mismatch'], 422);
        }

        $record->update(['consumed_at' => now()]);

        $this->auth->runAfter('otp.verify', ['identifier' => $record->identifier]);

        return response()->json(['valid' => true], 200);
    }
}
