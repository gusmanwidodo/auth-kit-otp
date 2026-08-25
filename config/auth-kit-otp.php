<?php

declare(strict_types=1);

return [
    /*
    | Seconds a generated OTP code stays valid.
    */
    'ttl' => (int) env('AUTH_KIT_OTP_TTL', 300),

    /*
    | Number of digits in a generated OTP code.
    */
    'length' => (int) env('AUTH_KIT_OTP_LENGTH', 6),
];
