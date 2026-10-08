<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CandidateRepositoryInterface;
use App\Services\CandidateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InviteController extends Controller
{
    public function __construct(
        private readonly CandidateRepositoryInterface $candidates,
        private readonly CandidateService $service,
    ) {
    }

    /**
     * Entry point from the email. The token identifies who the link belongs to;
     * the OTP proves the person holding it is really them.
     */
    public function open(Request $request, string $token): RedirectResponse|View
    {
        $candidate = $this->candidates->findByInviteToken($token);

        if (! $candidate || ! $candidate->inviteIsValid()) {
            return redirect()->route('candidate.link-expired');
        }

        $this->service->touchFirstAccess($candidate);

        // Remember which invite this session is working through.
        $request->session()->put('candidate_invite_token', $token);

        return redirect()->route('candidate.verify-phone');
    }

    public function expired(): View
    {
        return view('candidate.auth.expired');
    }
}
