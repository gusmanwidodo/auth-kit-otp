<?php

declare(strict_types=1);

namespace Gusmanwidodo\AuthKitOtp\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $identifier
 * @property string $code_hash
 * @property int $expires_at
 * @property \Illuminate\Support\Carbon|null $consumed_at
 */
class OtpCode extends Model
{
    protected $table = 'auth_kit_otp_codes';

    protected $fillable = [
        'identifier',
        'code_hash',
        'expires_at',
        'consumed_at',
    ];

    protected $casts = [
        'expires_at' => 'integer',
        'consumed_at' => 'datetime',
    ];
}
