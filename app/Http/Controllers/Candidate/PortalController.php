<?php

namespace App\Http\Controllers\Candidate;

use App\Enums\CandidateStatus;
use App\Http\Controllers\Controller;
use App\Enums\DocumentStatus;
use App\Http\Requests\Candidate\StoreReferencesRequest;
use App\Http\Requests\Candidate\UploadDocumentRequest;
use App\Models\CandidateDocument;
use App\Models\CandidateReference;
use App\Models\DocumentType;
use App\Services\CandidateService;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalController extends Controller
{
    public function __construct(
        private readonly DocumentService $documents,
        private readonly CandidateService $candidates,
    ) {
    }

    public function dashboard(): View
    {
        $candidate = Auth::guard('candidate')->user()
            ->load(['requirements', 'documents.documentType', 'references']);

        $uploaded = $candidate->documents->keyBy('document_type_id');

        return view('candidate.dashboard', [
            'candidate' => $candidate,
            'summary'   => $candidate->collectionSummary(),
            'checklist' => $candidate->requirements->map(fn ($type) => [
                'type'     => $type,
                'document' => $uploaded->get($type->id),
            ]),
            'canSubmit' => $candidate->hasSubmittedEverything()
                && $candidate->status !== CandidateStatus::Submitted,
        ]);
    }

    /** jQuery posts here; the page updates the card without reloading. */
    public function upload(UploadDocumentRequest $request): JsonResponse|RedirectResponse
    {
        $candidate = Auth::guard('candidate')->user();
        $type      = DocumentType::findOrFail($request->integer('document_type_id'));

        abort_unless($candidate->requirements->contains($type->id), 403, 'That document was not requested for you.');

        $document = $this->documents->store($candidate, $type, $request->file('file'));

        $candidate->load(['requirements', 'documents', 'references']);

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => $type->name.' uploaded.',
                'document' => [
                    'id'         => $document->id,
                    'name'       => $document->original_name,
                    'size'       => $document->size_label,
                    'status'      => $document->status->value,
                    'statusText'  => $document->status->label(),
                    'statusTone'  => $document->status->tone(),
                    'statusHint'  => $document->status->candidateHint(),
                    'uploadedAt'  => $document->updated_at->format('d M Y').' at '.$document->updated_at->format('H:i'),
                    'previewUrl'  => route('candidate.documents.preview', $document),
                    'downloadUrl' => route('candidate.documents.download', $document),
                ],
                'summary'   => $candidate->collectionSummary(),
                'canSubmit' => $candidate->hasSubmittedEverything(),
            ]);
        }

        return back()->with('status', $type->name.' uploaded.');
    }

    public function preview(CandidateDocument $document): StreamedResponse
    {
        $this->authorizeOwnership($document);

        return $this->documents->stream($document);
    }

    public function download(CandidateDocument $document): StreamedResponse
    {
        $this->authorizeOwnership($document);

        return $this->documents->download($document);
    }

    public function submit(): RedirectResponse
    {
        $candidate = Auth::guard('candidate')->user()->load(['requirements', 'documents', 'references']);

        if (! $candidate->hasSubmittedEverything()) {
            return back()->withErrors([
                'submit' => 'Some documents are still missing or need to be re-uploaded.',
            ]);
        }

        /*
         * When uploads are not auto-queued, submitting is what hands the whole
         * dossier to the recruiter — so move everything still sitting as
         * Uploaded into the review queue in one go.
         */
        if (! config('portal.review.auto_under_review', true)) {
            $candidate->documents()
                ->where('status', DocumentStatus::Uploaded)
                ->update(['status' => DocumentStatus::UnderReview]);
        }

        $this->candidates->markSubmitted($candidate);

        return redirect()->route('candidate.submitted');
    }

    /** Saves all three reference slots in one post. */
    public function saveReferences(StoreReferencesRequest $request): RedirectResponse
    {
        $candidate = Auth::guard('candidate')->user();

        $kept = $request->filledReferences();

        foreach ($kept as $slot => $row) {
            CandidateReference::updateOrCreate(
                ['candidate_id' => $candidate->id, 'slot' => (int) $slot],
                [
                    'name'         => $row['name'],
                    'relationship' => $row['relationship'] ?? null,
                    'organisation' => $row['organisation'] ?? null,
                    'email'        => $row['email'] ?? null,
                    'dial_code'    => $row['dial_code'] ?? null,
                    'phone'        => $row['phone'] ?? null,
                ]
            );
        }

        // A slot the candidate emptied should disappear, not linger as a stale row.
        $candidate->references()
            ->whereNotIn('slot', array_map('intval', array_keys($kept)))
            ->delete();

        return back()->with('status', count($kept)
            ? 'Your references have been saved.'
            : 'References cleared.');
    }

    public function submitted(): View
    {
        return view('candidate.submitted', [
            'candidate' => Auth::guard('candidate')->user(),
        ]);
    }

    public function signedOut(): View
    {
        return view('candidate.auth.signed-out');
    }

    private function authorizeOwnership(CandidateDocument $document): void
    {
        abort_unless(
            $document->candidate_id === Auth::guard('candidate')->id(),
            403,
            'This file does not belong to your application.'
        );
    }
}
