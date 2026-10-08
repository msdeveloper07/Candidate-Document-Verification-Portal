<?php

namespace App\Support\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VonageSmsGateway implements SmsGateway
{
    public function send(string $to, string $message): bool
    {
        $response = Http::asForm()->timeout(15)->post('https://rest.nexmo.com/sms/json', [
            'api_key'    => config('sms.vonage.key'),
            'api_secret' => config('sms.vonage.secret'),
            'from'       => config('sms.vonage.from'),
            'to'         => ltrim($to, '+'),
            'text'       => $message,
        ]);

        $status = data_get($response->json(), 'messages.0.status');

        if ($status !== '0') {
            Log::error('Vonage send failed', ['response' => $response->json()]);

            return false;
        }

        return true;
    }

    public function name(): string
    {
        return 'vonage';
    }
}
