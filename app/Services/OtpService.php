<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\Candidate;
use App\Models\OtpCode;
use App\Support\Sms\SmsGateway;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class OtpService
{
    public function __construct(
        private readonly SmsGateway $sms,
        private readonly ActivityLogger $logger,
    ) {
    }

    /**
     * Issue a fresh code, invalidating any earlier live codes for this number.
     *
     * @return array{otp: OtpCode, plain: string|null}
     */
    public function issue(Candidate $candidate, string $purpose = 'candidate_login'): array
    {
        $this->guardAgainstFlooding($candidate);

        // Retire outstanding codes so only the newest one works.
        OtpCode::query()
            ->where('dial_code', $candidate->dial_code)
            ->where('phone', $candidate->phone)
            ->where('purpose', $purpose)
            ->usable()
            ->update(['expires_at' => now()]);

        $plain = $this->generateCode();

        $otp = OtpCode::create([
            'candidate_id' => $candidate->id,
            'dial_code'    => $candidate->dial_code,
            'phone'        => $candidate->phone,
            'code_hash'    => Hash::make($plain),
            'purpose'      => $purpose,
            'channel'      => $this->sms->name(),
            'expires_at'   => now()->addMinutes(config('portal.otp.ttl_minutes')),
            'ip_address'   => request()->ip(),
            'user_agent'   => mb_substr((string) request()->userAgent(), 0, 255),
        ]);

        $this->deliver($candidate, $plain);

        $this->logger->record(
            'otp.sent',
            'Verification code sent to '.$candidate->masked_phone,
            $candidate,
            ['channel' => $this->sms->name()]
        );

        return [
            'otp'   => $otp,
            'plain' => config('portal.otp.expose_in_response') ? $plain : null,
        ];
    }

    /** @throws ValidationException */
    public function verify(Candidate $candidate, string $code, string $purpose = 'candidate_login'): OtpCode
    {
        $otp = OtpCode::query()
            ->where('dial_code', $candidate->dial_code)
            ->where('phone', $candidate->phone)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages([
                'code' => 'That code is no longer valid. Request a new one.',
            ]);
        }

        if ($otp->isExpired()) {
            throw ValidationException::withMessages([
                'code' => 'This code has expired. Request a new one.',
            ]);
        }

        if ($otp->attempts >= config('portal.otp.max_attempts')) {
            $otp->update(['expires_at' => now()]);

            throw ValidationException::withMessages([
                'code' => 'Too many incorrect attempts. Request a new code.',
            ]);
        }

        if (! $otp->matches($code)) {
            $otp->increment('attempts');
            $left = max(config('portal.otp.max_attempts') - $otp->attempts, 0);

            throw ValidationException::withMessages([
                'code' => $left > 0
                    ? "That code doesn't match. {$left} attempt".($left === 1 ? '' : 's').' left.'
                    : 'Too many incorrect attempts. Request a new code.',
            ]);
        }

        $otp->update(['verified_at' => now()]);

        return $otp;
    }

    public function secondsUntilResend(Candidate $candidate, string $purpose = 'candidate_login'): int
    {
        $last = OtpCode::query()
            ->where('dial_code', $candidate->dial_code)
            ->where('phone', $candidate->phone)
            ->where('purpose', $purpose)
            ->latest('id')
            ->first();

        if (! $last) {
            return 0;
        }

        $ready = $last->created_at->addSeconds(config('portal.otp.resend_cooldown'));

        return (int) max(now()->diffInSeconds($ready, false), 0);
    }

    private function generateCode(): string
    {
        $length = config('portal.otp.length');
        $max    = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    private function deliver(Candidate $candidate, string $plain): void
    {
        $minutes = config('portal.otp.ttl_minutes');
        $agency  = config('portal.agency_name');

        $this->sms->send(
            $candidate->dial_code.$candidate->phone,
            "{$plain} is your {$agency} verification code. It expires in {$minutes} minutes. Do not share it with anyone."
        );

        if (config('portal.otp.also_email') && filled($candidate->email)) {
            // Email is a convenience here; SMS is the real channel, so never
            // let a mail failure stop a candidate from logging in.
            try {
                Mail::to($candidate->email)->send(new OtpMail($candidate, $plain));
            } catch (Throwable $e) {
                Log::warning('OTP email failed', [
                    'candidate_id' => $candidate->id,
                    'error'        => $e->getMessage(),
                ]);
            }
        }
    }

    /** @throws ValidationException */
    private function guardAgainstFlooding(Candidate $candidate): void
    {
        $wait = $this->secondsUntilResend($candidate);

        if ($wait > 0) {
            throw ValidationException::withMessages([
                'phone' => "Wait {$wait} seconds before requesting another code.",
            ]);
        }

        $sentThisHour = OtpCode::query()
            ->where('dial_code', $candidate->dial_code)
            ->where('phone', $candidate->phone)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($sentThisHour >= config('portal.otp.max_sends_per_hour')) {
            throw ValidationException::withMessages([
                'phone' => 'You have requested too many codes. Try again in an hour or contact the agency.',
            ]);
        }
    }
}
