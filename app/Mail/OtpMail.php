<?php

namespace App\Mail;

use App\Models\Candidate;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    protected array $tpl;

    public function __construct(public Candidate $candidate, public string $code)
    {
        $this->tpl = EmailTemplate::render(EmailTemplate::OTP, [
            'agency_name'  => config('portal.agency_name'),
            'first_name'   => $candidate->first_name,
            'full_name'    => $candidate->full_name,
            'reference_no' => $candidate->reference_no,
            'otp_code'     => $code,
            'otp_minutes'  => (int) config('portal.otp.ttl_minutes'),
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->tpl['subject']);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.templated',
            with: [
                'tpl'     => $this->tpl,
                'otpCode' => $this->code,
            ],
        );
    }
}
