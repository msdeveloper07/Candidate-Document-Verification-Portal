<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * A signed-in candidate stays signed in only while their invite is live and
 * their record is not archived.
 */
class EnsureCandidateInviteIsLive
{
    public function handle(Request $request, Closure $next): Response
    {
        $candidate = Auth::guard('candidate')->user();

        if ($candidate && ! $candidate->inviteIsValid()) {
            Auth::guard('candidate')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('candidate.link-expired');
        }

        return $next($request);
    }
}
