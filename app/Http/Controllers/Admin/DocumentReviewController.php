<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewDocumentRequest;
use App\Models\CandidateDocument;
use App\Repositories\Contracts\CandidateDocumentRepositoryInterface;
use App\Repositories\Contracts\DocumentTypeRepositoryInterface;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentReviewController extends Controller
{
    public function __construct(
        private readonly CandidateDocumentRepositoryInterface $documents,
        private readonly DocumentTypeRepositoryInterface $documentTypes,
        private readonly DocumentService $service,
    ) {
    }

    public function index(Request $request): View
    {
        return view('admin.documents.index', [
            'documents' => $this->documents->pendingReview(
                config('portal.pagination.per_page'),
                $request->only(['status', 'type', 'search'])
            ),
            'types'   => $this->documentTypes->active(),
            'filters' => $request->only(['status', 'type', 'search']),
            'stats'   => $this->documents->statusCounts(),
        ]);
    }

    public function review(ReviewDocumentRequest $request, CandidateDocument $document): RedirectResponse
    {
        if ($request->input('decision') === 'approve') {
            $this->service->approve($document, $request->input('remarks'));
            $message = $document->documentType->name.' approved.';
        } else {
            $this->service->reject($document, $request->input('remarks'));
            $message = $document->documentType->name.' sent back for re-upload.';
        }

        return back()->with('status', $message);
    }

    /** Inline preview inside the review drawer. */
    public function preview(CandidateDocument $document): StreamedResponse
    {
        return $this->service->stream($document);
    }

    public function download(CandidateDocument $document): StreamedResponse
    {
        return $this->service->download($document);
    }

    public function destroy(CandidateDocument $document): RedirectResponse
    {
        $candidate = $document->candidate;
        $this->service->purge($document);

        return redirect()
            ->route('admin.candidates.show', $candidate)
            ->with('status', 'Document removed. The candidate can upload a replacement.');
    }
}
