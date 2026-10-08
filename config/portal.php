<?php

return [
    'agency_name' => env('AGENCY_NAME', 'Cyberbells LLC'),

    'invite' => [
        // How long a candidate's upload link stays usable.
        'valid_days' => (int) env('INVITE_VALID_DAYS', 14),
    ],

    'otp' => [
        'length'            => (int) env('OTP_LENGTH', 6),
        'ttl_minutes'       => (int) env('OTP_TTL_MINUTES', 10),
        'max_attempts'      => (int) env('OTP_MAX_ATTEMPTS', 5),
        'resend_cooldown'   => (int) env('OTP_RESEND_COOLDOWN', 60), // seconds
        'max_sends_per_hour'=> (int) env('OTP_MAX_SENDS_PER_HOUR', 6),
        // Mirror the code to the candidate's email as well as SMS.
        'also_email'        => (bool) env('OTP_ALSO_EMAIL', true),
        // Local only: show the code on screen so you can test without a gateway.
        'expose_in_response'=> (bool) env('OTP_EXPOSE', false),
    ],

    'review' => [
        /*
         * true  — an upload lands straight in the recruiter's queue (Under review).
         * false — it sits as Uploaded until the candidate submits the whole dossier.
         */
        'auto_under_review' => (bool) env('REVIEW_AUTO_UNDER_REVIEW', true),
    ],

    'uploads' => [
        'disk'            => env('DOCUMENT_DISK', 'documents'),
        'max_size_kb'      => (int) env('DOCUMENT_MAX_SIZE_KB', 8192),
        'blocked_extensions' => ['php', 'phtml', 'exe', 'sh', 'bat', 'js', 'html', 'htm', 'svg'],
    ],

    'pagination' => [
        'per_page' => 15,
    ],
];
