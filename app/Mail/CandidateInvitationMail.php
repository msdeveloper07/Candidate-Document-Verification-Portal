<?php

namespace App\Mail;

use App\Models\Candidate;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected array $tpl;

    public function __construct(public Candidate $candidate)
    {
        $this->candidate->loadMissing('requirements');

        $this->tpl = EmailTemplate::render(EmailTemplate::INVITATION, [
            'agency_name'   => config('portal.agency_name'),
            'first_name'    => $candidate->first_name,
            'full_name'     => $candidate->full_name,
            'reference_no'  => $candidate->reference_no,
            'upload_link'   => $candidate->invite_url,
            'expiry_date'   => $candidate->invite_expires_at?->format('d F Y'),
            'document_list' => $candidate->requirements->pluck('name')->implode("\n"),
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
                'tpl'       => $this->tpl,
                'reference' => $this->candidate->reference_no,
                'link'      => $this->candidate->invite_url,
                'documents' => $this->candidate->requirements,
            ],
        );
    }
}
