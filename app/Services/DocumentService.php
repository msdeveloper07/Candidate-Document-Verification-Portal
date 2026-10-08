<?php

namespace App\Services;

use App\Enums\CandidateStatus;
use App\Enums\DocumentStatus;
use App\Mail\DocumentReviewedMail;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Models\DocumentRevision;
use App\Models\DocumentType;
use App\Repositories\Contracts\CandidateDocumentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    public function __construct(
        private readonly CandidateDocumentRepositoryInterface $documents,
        private readonly ActivityLogger $logger,
    ) {
    }

    /**
     * Store an upload against a requirement. Replacing an existing file archives
     * the old one into document_revisions rather than deleting it.
     */
    public function store(Candidate $candidate, DocumentType $type, UploadedFile $file): CandidateDocument
    {
        $this->assertAllowed($type, $file);

        /*
         * Read every attribute of the upload BEFORE storing it. storeAs() moves
         * the temporary file, after which getSize() and getRealPath() are gone.
         */
        $meta = [
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 190),
            'mime_type'     => $file->getClientMimeType(),
            'extension'     => Str::lower($file->getClientOriginalExtension()),
            'size_bytes'    => $file->getSize(),
            'checksum'      => hash_file('sha256', $file->getRealPath()),
        ];

        return DB::transaction(function () use ($candidate, $type, $file, $meta) {
            $disk = config('portal.uploads.disk');

            $filename = sprintf(
                '%s-%s.%s',
                $type->slug,
                Str::lower(Str::random(10)),
                $meta['extension']
            );

            $path = $file->storeAs(
                'candidates/'.$candidate->reference_no,
                $filename,
                ['disk' => $disk]
            );

            $existing = $this->documents->existing($candidate->id, $type->id);
            $version  = 1;

            if ($existing) {
                $this->archive($existing);
                $version = $existing->version + 1;
            }

            $attributes = [
                ...$meta,
                'candidate_id'     => $candidate->id,
                'document_type_id' => $type->id,
                'file_path'        => $path,
                'disk'             => $disk,
                'status'           => config('portal.review.auto_under_review', true)
                    ? DocumentStatus::UnderReview
                    : DocumentStatus::Uploaded,
                'remarks'          => null,
                'version'          => $version,
                'uploaded_ip'      => request()->ip(),
                'reviewed_by'      => null,
                'reviewed_at'      => null,
            ];

            $document = $existing
                ? tap($existing)->update($attributes)
                : CandidateDocument::create($attributes);

            if ($candidate->status === CandidateStatus::Invited) {
                $candidate->forceFill(['status' => CandidateStatus::InProgress])->save();
            }

            $this->logger->record(
                'document.uploaded',
                ($version > 1 ? 'Replaced ' : 'Uploaded ').$type->name,
                $candidate,
                ['document_type' => $type->name, 'version' => $version, 'size' => $meta['size_bytes']]
            );

            return $document->fresh('documentType');
        });
    }

    public function approve(CandidateDocument $document, ?string $remarks = null): CandidateDocument
    {
        return $this->review($document, DocumentStatus::Approved, $remarks);
    }

    public function reject(CandidateDocument $document, string $remarks): CandidateDocument
    {
        return $this->review($document, DocumentStatus::Rejected, $remarks);
    }

    private function review(CandidateDocument $document, DocumentStatus $status, ?string $remarks): CandidateDocument
    {
        return DB::transaction(function () use ($document, $status, $remarks) {
            $document->update([
                'status'      => $status,
                'remarks'     => $remarks,
                'reviewed_by' => Auth::guard('admin')->id(),
                'reviewed_at' => now(),
            ]);

            $candidate = $document->candidate;

            $this->logger->record(
                'document.'.$status->value,
                $document->documentType->name.' — '.$status->label(),
                $candidate,
                ['remarks' => $remarks]
            );

            $this->recalculateCandidateStatus($candidate);

            if (filled($candidate->email)) {
                // The decision is already saved; a mail problem must not roll it back.
                try {
                    Mail::to($candidate->email)->send(new DocumentReviewedMail($candidate, $document->fresh('documentType')));
                } catch (Throwable $e) {
                    Log::warning('Review email failed', [
                        'document_id' => $document->id,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }

            return $document->fresh(['documentType', 'reviewedBy']);
        });
    }

    /** Keeps the candidate's headline status honest after each review action. */
    public function recalculateCandidateStatus(Candidate $candidate): void
    {
        $candidate->load(['requirements', 'documents', 'references']);
        $summary = $candidate->collectionSummary();

        $allDocsApproved = $summary['approved'] === $summary['total'] && $summary['total'] > 0;

        $status = match (true) {
            $summary['total'] === 0                              => $candidate->status,
            $summary['rejected'] > 0                             => CandidateStatus::Rejected,
            $allDocsApproved                                     => CandidateStatus::Approved,
            $summary['missing'] === 0 && $summary['pending'] > 0 => CandidateStatus::UnderReview,
            default                                              => CandidateStatus::InProgress,
        };

        if ($status !== $candidate->status) {
            $candidate->forceFill([
                'status'      => $status,
                'reviewed_at' => $status === CandidateStatus::Approved ? now() : $candidate->reviewed_at,
                'reviewed_by' => $status === CandidateStatus::Approved
                    ? (Auth::guard('admin')->id() ?? $candidate->reviewed_by)
                    : $candidate->reviewed_by,
            ])->save();
        }
    }

    public function download(CandidateDocument $document)
    {
        abort_unless($document->fileExists(), 404, 'This file is no longer on the server.');

        return Storage::disk($document->disk)->download($document->file_path, $document->original_name);
    }

    public function stream(CandidateDocument $document)
    {
        abort_unless($document->fileExists(), 404, 'This file is no longer on the server.');

        return Storage::disk($document->disk)->response(
            $document->file_path,
            $document->original_name,
            ['Content-Type' => $document->mime_type ?: 'application/octet-stream']
        );
    }

    public function purge(CandidateDocument $document): void
    {
        DB::transaction(function () use ($document) {
            foreach ($document->revisions as $revision) {
                Storage::disk($revision->disk)->delete($revision->file_path);
            }

            Storage::disk($document->disk)->delete($document->file_path);

            $this->logger->record(
                'document.deleted',
                'Removed '.$document->documentType->name,
                $document->candidate
            );

            $document->delete();
        });
    }

    private function archive(CandidateDocument $document): void
    {
        DocumentRevision::create([
            'candidate_document_id'  => $document->id,
            'original_name'          => $document->original_name,
            'file_path'              => $document->file_path,
            'disk'                   => $document->disk,
            'mime_type'              => $document->mime_type,
            'size_bytes'             => $document->size_bytes,
            'version'                => $document->version,
            'status_at_replacement'  => $document->status->value,
            'remarks_at_replacement' => $document->remarks,
            'replaced_at'            => now(),
        ]);
    }

    /** @throws ValidationException */
    private function assertAllowed(DocumentType $type, UploadedFile $file): void
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        if (in_array($extension, config('portal.uploads.blocked_extensions'), true)) {
            throw ValidationException::withMessages([
                'file' => 'That file type cannot be uploaded.',
            ]);
        }

        if (! in_array($extension, array_map('strtolower', $type->allowed_extensions), true)) {
            throw ValidationException::withMessages([
                'file' => 'Use one of these formats: '.$type->extension_list.'.',
            ]);
        }

        if ($file->getSize() > $type->max_size_kb * 1024) {
            throw ValidationException::withMessages([
                'file' => 'Keep the file under '.$type->max_size_label.'.',
            ]);
        }
    }
}
