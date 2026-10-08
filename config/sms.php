<?php

return [
    /*
    | log    — writes to storage/logs (use in local development)
    | twilio — https://www.twilio.com
    | vonage — https://developer.vonage.com
    */
    'driver'      => env('SMS_DRIVER', 'log'),
    'log_channel' => env('SMS_LOG_CHANNEL', 'stack'),

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],

    'vonage' => [
        'key'    => env('VONAGE_KEY'),
        'secret' => env('VONAGE_SECRET'),
        'from'   => env('VONAGE_FROM', 'Cyberbells'),
    ],
];
