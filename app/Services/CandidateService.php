<?php

namespace App\Services;

use App\Enums\CandidateStatus;
use App\Mail\CandidateInvitationMail;
use App\Models\Candidate;
use App\Repositories\Contracts\CandidateRepositoryInterface;
use App\Repositories\Contracts\DocumentTypeRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class CandidateService
{
    /** Set when the last invitation email could not be handed to the mailer. */
    public ?string $lastDeliveryError = null;

    public function invitationWasDelivered(): bool
    {
        return $this->lastDeliveryError === null;
    }

    public function __construct(
        private readonly CandidateRepositoryInterface $candidates,
        private readonly DocumentTypeRepositoryInterface $documentTypes,
        private readonly ActivityLogger $logger,
    ) {
    }

    /**
     * Create the candidate, attach their document checklist and send the link.
     *
     * @param  array<int>  $requirementIds
     */
    public function invite(array $data, array $requirementIds = [], bool $sendMail = true): Candidate
    {
        return DB::transaction(function () use ($data, $requirementIds, $sendMail) {
            $candidate = $this->candidates->create([
                ...$data,
                'status'            => CandidateStatus::Invited,
                'invited_by'        => Auth::guard('admin')->id(),
                'invite_token'      => $this->freshToken(),
                'invite_sent_at'    => $sendMail ? now() : null,
                'invite_expires_at' => now()->addDays(config('portal.invite.valid_days')),
            ]);

            $this->syncRequirements($candidate, $requirementIds);

            $this->logger->record(
                'candidate.created',
                'Candidate record created',
                $candidate,
                ['reference' => $candidate->reference_no]
            );

            if ($sendMail) {
                $this->sendInvitation($candidate, resend: false);
            }

            return $candidate->fresh(['requirements', 'documents']);
        });
    }

    public function update(Candidate $candidate, array $data, array $requirementIds = []): Candidate
    {
        return DB::transaction(function () use ($candidate, $data, $requirementIds) {
            $candidate->update($data);
            $this->syncRequirements($candidate, $requirementIds);

            $this->logger->record('candidate.updated', 'Candidate details updated', $candidate);

            return $candidate->fresh(['requirements', 'documents']);
        });
    }

    /** Issues a brand new token, so old links stop working. */
    public function sendInvitation(Candidate $candidate, bool $resend = true): Candidate
    {
        if ($resend || blank($candidate->invite_token) || ! $candidate->inviteIsValid()) {
            $candidate->forceFill([
                'invite_token'      => $this->freshToken(),
                'invite_expires_at' => now()->addDays(config('portal.invite.valid_days')),
            ])->save();
        }

        $candidate = $candidate->fresh();

        /*
         * A mail failure must not lose the candidate or the token — the link is
         * already valid and the recruiter can copy it by hand. So we record what
         * happened and let the caller tell the user, rather than throwing.
         */
        $this->lastDeliveryError = null;

        try {
            Mail::to($candidate->email)->send(new CandidateInvitationMail($candidate));
            $candidate->forceFill(['invite_sent_at' => now()])->save();
            $delivered = true;
        } catch (Throwable $e) {
            $this->lastDeliveryError = $e->getMessage();
            $delivered = false;
            Log::error('Invitation email failed', [
                'candidate_id' => $candidate->id,
                'email'        => $candidate->email,
                'error'        => $e->getMessage(),
            ]);
        }

        $this->logger->record(
            match (true) {
                ! $delivered => 'invite.failed',
                $resend      => 'invite.resent',
                default      => 'invite.sent',
            },
            $delivered
                ? ($resend ? 'Upload link re-sent to '.$candidate->email : 'Upload link sent to '.$candidate->email)
                : 'Email delivery failed — the link must be shared manually',
            $candidate,
            [
                'expires_at' => $candidate->invite_expires_at?->toDateTimeString(),
                'delivered'  => $delivered,
                'error'      => $this->lastDeliveryError,
            ]
        );

        return $candidate->fresh();
    }

    public function revokeInvite(Candidate $candidate): void
    {
        $candidate->forceFill(['invite_token' => null, 'invite_expires_at' => null])->save();

        $this->logger->record('invite.revoked', 'Upload link revoked', $candidate);
    }

    /** Candidate presses "Submit for review" once every required file is in. */
    public function markSubmitted(Candidate $candidate): Candidate
    {
        $candidate->forceFill([
            'status'       => CandidateStatus::Submitted,
            'submitted_at' => now(),
        ])->save();

        $this->logger->record('candidate.submitted', 'Candidate submitted their documents for review', $candidate);

        return $candidate;
    }

    public function changeStatus(Candidate $candidate, CandidateStatus $status, ?string $note = null): Candidate
    {
        $candidate->forceFill([
            'status'      => $status,
            'reviewed_at' => in_array($status, [CandidateStatus::Approved, CandidateStatus::Rejected], true) ? now() : $candidate->reviewed_at,
            'reviewed_by' => in_array($status, [CandidateStatus::Approved, CandidateStatus::Rejected], true) ? Auth::guard('admin')->id() : $candidate->reviewed_by,
        ])->save();

        $this->logger->record(
            'candidate.status_changed',
            'Status changed to '.$status->label(),
            $candidate,
            ['note' => $note]
        );

        return $candidate;
    }

    /** Nudges the candidate from "invited" to "uploading" the first time they land. */
    public function touchFirstAccess(Candidate $candidate): void
    {
        if (blank($candidate->first_accessed_at)) {
            $candidate->forceFill(['first_accessed_at' => now()])->save();
        }

        if ($candidate->status === CandidateStatus::Invited) {
            $candidate->forceFill(['status' => CandidateStatus::InProgress])->save();
        }
    }

    public function syncRequirements(Candidate $candidate, array $requirementIds): void
    {
        if (empty($requirementIds)) {
            $requirementIds = $this->documentTypes->defaults()->pluck('id')->all();
        }

        $payload = collect($requirementIds)
            ->mapWithKeys(fn ($id) => [(int) $id => ['is_required' => true]])
            ->all();

        $candidate->requirements()->sync($payload);
    }

    private function freshToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Candidate::withTrashed()->where('invite_token', $token)->exists());

        return $token;
    }
}
