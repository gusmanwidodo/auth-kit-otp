<?php

declare(strict_types=1);

use Gusmanwidodo\AuthKit\AuthManager;
use Gusmanwidodo\AuthKitOtp\Models\OtpCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('self-registers the otp plugin into the core registry', function () {
    $manager = app(AuthManager::class);

    expect($manager->registry()->has('otp'))->toBeTrue();
});

it('mounts the plugin routes under the core prefix', function () {
    $response = $this->postJson('/auth-kit/otp/issue', [
        'identifier' => 'user@example.com',
    ]);

    $response->assertCreated()
        ->assertJson(['issued' => true]);

    expect(OtpCode::where('identifier', 'user@example.com')->exists())->toBeTrue();
});

it('runs the plugin migration from the separate package', function () {
    // If the core did not pick up the plugin's migrationPaths(), this table
    // would not exist and the query would throw.
    expect(OtpCode::count())->toBe(0);
});

it('issues then verifies a valid code end-to-end', function () {
    $issue = $this->postJson('/auth-kit/otp/issue', [
        'identifier' => 'alice@example.com',
    ])->assertCreated();

    $code = $issue->json('code');
    expect($code)->not->toBeNull();

    $this->postJson('/auth-kit/otp/verify', [
        'identifier' => 'alice@example.com',
        'code' => $code,
    ])->assertOk()->assertJson(['valid' => true]);
});

it('rejects a wrong code', function () {
    $this->postJson('/auth-kit/otp/issue', ['identifier' => 'bob@example.com'])
        ->assertCreated();

    $this->postJson('/auth-kit/otp/verify', [
        'identifier' => 'bob@example.com',
        'code' => '000000',
    ])->assertStatus(422)->assertJson(['valid' => false, 'reason' => 'mismatch']);
});

it('rejects an expired code via the core before-hook pipeline', function () {
    // Insert a code that is already expired.
    OtpCode::create([
        'identifier' => 'carol@example.com',
        'code_hash' => Illuminate\Support\Facades\Hash::make('123456'),
        'expires_at' => now()->timestamp - 10,
    ]);

    $this->postJson('/auth-kit/otp/verify', [
        'identifier' => 'carol@example.com',
        'code' => '123456',
    ])->assertStatus(422)->assertJson(['valid' => false, 'reason' => 'expired']);
});

it('consumes a code so it cannot be reused', function () {
    $code = $this->postJson('/auth-kit/otp/issue', ['identifier' => 'dave@example.com'])
        ->json('code');

    $this->postJson('/auth-kit/otp/verify', [
        'identifier' => 'dave@example.com',
        'code' => $code,
    ])->assertOk();

    // Second attempt: record is consumed, so it is treated as not found.
    $this->postJson('/auth-kit/otp/verify', [
        'identifier' => 'dave@example.com',
        'code' => $code,
    ])->assertStatus(422)->assertJson(['reason' => 'not_found']);
});
