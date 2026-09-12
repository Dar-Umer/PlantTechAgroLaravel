<?php

return [
    // When true, the API echoes the generated OTP inside the JSON response.
    // Useful for development/testing; disable in production.
    'echo_otp' => env('API_ECHO_OTP', env('APP_ENV') !== 'production'),

    'otp_expires_minutes' => 15,
];