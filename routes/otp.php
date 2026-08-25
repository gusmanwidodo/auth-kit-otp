<?php

declare(strict_types=1);

use Gusmanwidodo\AuthKitOtp\Http\OtpController;
use Illuminate\Support\Facades\Route;

// Mounted under "{prefix}/otp" by the Auth-Kit core (default: /auth-kit/otp).
Route::post('/issue', [OtpController::class, 'issue'])->name('auth-kit.otp.issue');
Route::post('/verify', [OtpController::class, 'verify'])->name('auth-kit.otp.verify');
