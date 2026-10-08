<?php

namespace App\Mail;

use App\Enums\DocumentStatus;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentReviewedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected array $tpl;
    protected string $link;

    public function __construct(public Candidate $candidate, public CandidateDocument $document)
    {
        $this->document->loadMissing('documentType');

        // An expired invite would send them to a dead link, so fall back.
        $this->link = $candidate->inviteIsValid()
            ? $candidate->invite_url
            : route('candidate.link-expired');

        $this->tpl = EmailTemplate::render(EmailTemplate::REVIEWED, [
            'agency_name'   => config('portal.agency_name'),
            'first_name'    => $candidate->first_name,
            'full_name'     => $candidate->full_name,
            'reference_no'  => $candidate->reference_no,
            'document_name' => $document->documentType?->name ?? 'document',
            'decision'      => $document->status->label(),
            'remarks'       => $document->remarks,
            'upload_link'   => $this->link,
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
                'tpl'          => $this->tpl,
                'reference'    => $this->candidate->reference_no,
                'link'         => $this->link,
                'decisionNote' => $this->document->remarks,
                'decisionTone' => $this->document->status === DocumentStatus::Rejected ? 'danger' : 'success',
            ],
        );
    }
}
