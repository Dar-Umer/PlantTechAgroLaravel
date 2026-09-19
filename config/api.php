<?php

return [
    // When true, the API echoes the generated OTP inside the JSON response.
    // SECURITY: must stay false in production. Enable explicitly only for
    // local development via API_ECHO_OTP=true.
    'echo_otp' => env('API_ECHO_OTP', false),

    'otp_expires_minutes' => 10,

    // Max verification attempts per OTP window before the OTP is invalidated.
    'otp_max_attempts' => 5,
];