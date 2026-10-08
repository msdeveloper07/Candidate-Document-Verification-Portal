<?php

namespace App\Support\Sms;

/**
 * Swap providers without touching the OTP flow. Bind a concrete gateway in
 * RepositoryServiceProvider and set SMS_DRIVER in .env.
 */
interface SmsGateway
{
    /** @return bool True when the provider accepted the message. */
    public function send(string $to, string $message): bool;

    public function name(): string;
}
