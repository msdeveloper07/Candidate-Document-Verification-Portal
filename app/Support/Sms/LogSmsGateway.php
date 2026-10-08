<?php

namespace App\Support\Sms;

use Illuminate\Support\Facades\Log;

/** Development driver — writes the SMS to the log instead of sending it. */
class LogSmsGateway implements SmsGateway
{
    public function send(string $to, string $message): bool
    {
        Log::channel(config('sms.log_channel', 'stack'))
            ->info('[SMS OUT] '.$to.' :: '.$message);

        return true;
    }

    public function name(): string
    {
        return 'log';
    }
}
