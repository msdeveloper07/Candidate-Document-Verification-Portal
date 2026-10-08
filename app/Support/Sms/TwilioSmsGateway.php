<?php

namespace App\Support\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioSmsGateway implements SmsGateway
{
    public function send(string $to, string $message): bool
    {
        $sid   = config('sms.twilio.sid');
        $token = config('sms.twilio.token');
        $from  = config('sms.twilio.from');

        if (blank($sid) || blank($token)) {
            Log::error('Twilio credentials are missing; SMS not sent.');

            return false;
        }

        $response = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->timeout(15)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To'   => $to,
                'From' => $from,
                'Body' => $message,
            ]);

        if ($response->failed()) {
            Log::error('Twilio send failed', ['status' => $response->status(), 'body' => $response->body()]);
        }

        return $response->successful();
    }

    public function name(): string
    {
        return 'twilio';
    }
}
