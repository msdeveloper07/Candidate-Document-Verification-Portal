<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidate\SendOtpRequest;
use App\Http\Requests\Candidate\VerifyOtpRequest;
use App\Models\Candidate;
use App\Repositories\Contracts\CandidateRepositoryInterface;
use App\Services\ActivityLogger;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OtpAuthController extends Controller
{
    public function __construct(
        private readonly CandidateRepositoryInterface $candidates,
        private readonly OtpService $otp,
        private readonly ActivityLogger $logger,
    ) {
    }

    /** Step 1 — confirm the mobile number on file. */
    public function showPhoneForm(Request $request): RedirectResponse|View
    {
        $candidate = $this->candidateFromSession($request);

        if (! $candidate) {
            return redirect()->route('candidate.link-expired');
        }

        return view('candidate.auth.phone', [
            'candidate' => $candidate,
            'hint'      => $candidate->masked_phone,
        ]);
    }

    public function sendOtp(SendOtpRequest $request): RedirectResponse
    {
        $candidate = $this->candidateFromSession($request);

        if (! $candidate) {
            return redirect()->route('candidate.link-expired');
        }

        // The number typed must match the one the agency invited.
        if ($candidate->dial_code !== $request->input('dial_code') || $candidate->phone !== $request->input('phone')) {
            $this->logger->record(
                'otp.phone_mismatch',
                'Someone entered a number that does not match this invitation',
                $candidate
            );

            throw ValidationException::withMessages([
                'phone' => 'That number does not match the one on this invitation. Check with the agency if it has changed.',
            ]);
        }

        $result = $this->otp->issue($candidate);

        $request->session()->put('candidate_otp_phone', $candidate->dial_code.$candidate->phone);

        return redirect()
            ->route('candidate.verify-code')
            ->with('status', 'We sent a code to '.$candidate->masked_phone.'.')
            ->with('dev_otp', $result['plain']);
    }

    /** Step 2 — enter the code. */
    public function showCodeForm(Request $request): RedirectResponse|View
    {
        $candidate = $this->candidateFromSession($request);

        if (! $candidate || ! $request->session()->has('candidate_otp_phone')) {
            return redirect()->route('candidate.verify-phone');
        }

        return view('candidate.auth.code', [
            'candidate'     => $candidate,
            'resendIn'      => $this->otp->secondsUntilResend($candidate),
            'codeLength'    => config('portal.otp.length'),
            'expiryMinutes' => config('portal.otp.ttl_minutes'),
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): RedirectResponse
    {
        $candidate = $this->candidateFromSession($request);

        if (! $candidate) {
            return redirect()->route('candidate.link-expired');
        }

        $this->otp->verify($candidate, $request->input('code'));

        Auth::guard('candidate')->login($candidate);
        $request->session()->regenerate();
        $request->session()->put('candidate_invite_token', $candidate->invite_token);
        $request->session()->forget('candidate_otp_phone');

        $this->logger->record('candidate.signed_in', 'Candidate verified their number and signed in', $candidate);

        return redirect()->route('candidate.dashboard');
    }

    public function resend(Request $request): RedirectResponse
    {
        $candidate = $this->candidateFromSession($request);

        if (! $candidate) {
            return redirect()->route('candidate.link-expired');
        }

        $result = $this->otp->issue($candidate);

        return back()
            ->with('status', 'A new code is on its way.')
            ->with('dev_otp', $result['plain']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('candidate')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('candidate.signed-out');
    }

    private function candidateFromSession(Request $request): ?Candidate
    {
        $token = $request->session()->get('candidate_invite_token');

        if (blank($token)) {
            return null;
        }

        $candidate = $this->candidates->findByInviteToken($token);

        return $candidate?->inviteIsValid() ? $candidate : null;
    }
}
