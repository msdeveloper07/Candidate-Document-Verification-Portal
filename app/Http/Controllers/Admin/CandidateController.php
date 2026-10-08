<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CandidateStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCandidateRequest;
use App\Models\Candidate;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\AdminRepositoryInterface;
use App\Repositories\Contracts\CandidateRepositoryInterface;
use App\Repositories\Contracts\DocumentTypeRepositoryInterface;
use App\Services\CandidateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function __construct(
        private readonly CandidateRepositoryInterface $candidates,
        private readonly DocumentTypeRepositoryInterface $documentTypes,
        private readonly AdminRepositoryInterface $admins,
        private readonly ActivityLogRepositoryInterface $activity,
        private readonly CandidateService $service,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'recruiter', 'from', 'to']);

        return view('admin.candidates.index', [
            'candidates' => $this->candidates->paginate(config('portal.pagination.per_page'), $filters),
            'statuses'   => CandidateStatus::options(),
            'recruiters' => $this->admins->activeRecruiters(),
            'filters'    => $filters,
            'counts'     => $this->candidates->dashboardCounts(),
        ]);
    }

    public function create(): View
    {
        return view('admin.candidates.create', [
            'documentTypes' => $this->documentTypes->active(),
            'preselected'   => $this->documentTypes->defaults()->pluck('id')->all(),
            'countries'     => config('countries'),
        ]);
    }

    public function store(StoreCandidateRequest $request): RedirectResponse
    {
        $candidate = $this->service->invite(
            $request->candidateData(),
            $request->input('requirements', []),
            $request->boolean('send_invite', true)
        );

        $redirect = redirect()->route('admin.candidates.show', $candidate);

        if (! $request->boolean('send_invite', true)) {
            return $redirect->with('status', 'Candidate added. Send the upload link when you are ready.');
        }

        if ($this->service->invitationWasDelivered()) {
            return $redirect->with('status', 'Candidate added and the upload link is on its way to '.$candidate->email.'.');
        }

        // Mail is down, but the link itself is valid — surface it for copying.
        return $redirect
            ->with('warning', 'Candidate added, but the email could not be sent. Copy the link below and send it yourself.')
            ->with('highlight_invite', true);
    }

    public function show(Candidate $candidate): View
    {
        $candidate->load([
            'requirements',
            'references',
            'documents.documentType',
            'documents.reviewedBy:id,name',
            'documents.revisions',
            'invitedBy:id,name',
            'reviewedBy:id,name',
        ]);

        return view('admin.candidates.show', [
            'candidate' => $candidate,
            'summary'   => $candidate->collectionSummary(),
            'timeline'  => $this->activity->forCandidate($candidate->id),
            'statuses'  => CandidateStatus::options(),
        ]);
    }

    public function edit(Candidate $candidate): View
    {
        $candidate->load('requirements');

        return view('admin.candidates.edit', [
            'candidate'     => $candidate,
            'documentTypes' => $this->documentTypes->active(),
            'preselected'   => $candidate->requirements->pluck('id')->all(),
            'countries'     => config('countries'),
        ]);
    }

    public function update(StoreCandidateRequest $request, Candidate $candidate): RedirectResponse
    {
        $this->service->update($candidate, $request->candidateData(), $request->input('requirements', []));

        return redirect()
            ->route('admin.candidates.show', $candidate)
            ->with('status', 'Candidate details updated.');
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        $candidate->delete();

        return redirect()
            ->route('admin.candidates.index')
            ->with('status', $candidate->full_name.' was archived.');
    }

    public function resendInvite(Candidate $candidate): RedirectResponse
    {
        $this->service->sendInvitation($candidate);

        if (! $this->service->invitationWasDelivered()) {
            return back()
                ->with('warning', 'The email could not be sent. A fresh link was still generated — copy it below.')
                ->with('highlight_invite', true);
        }

        return back()->with('status', 'A new upload link was sent to '.$candidate->email.'.');
    }

    public function revokeInvite(Candidate $candidate): RedirectResponse
    {
        $this->service->revokeInvite($candidate);

        return back()->with('status', 'The upload link no longer works.');
    }

    public function changeStatus(Request $request, Candidate $candidate): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(CandidateStatus::options()))],
            'note'   => ['nullable', 'string', 'max:500'],
        ]);

        $this->service->changeStatus($candidate, CandidateStatus::from($data['status']), $data['note'] ?? null);

        return back()->with('status', 'Status changed to '.CandidateStatus::from($data['status'])->label().'.');
    }
}
