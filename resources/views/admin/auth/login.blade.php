<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
    <link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
</head>
<body>
<div class="auth-split">

    <section class="auth-aside">
        <div class="d-flex align-items-center gap-2 position-relative">
            <span class="seal" style="width:32px;height:32px;border-radius:8px;display:grid;place-items:center;background:linear-gradient(145deg,var(--brass-bright),var(--brass));color:var(--ink);font-family:var(--font-display);font-weight:700;font-size:.85rem;">{{ mb_substr(config('portal.agency_name'), 0, 1) }}</span>
            <span style="font-family:var(--font-display);font-weight:600;color:#fff;">{{ config('portal.agency_name') }}</span>
        </div>

        <div>
            <p class="lede">Every document, <em>in one place</em>, not an inbox.</p>
            <p class="sub">
                Send one link. The candidate confirms their number, uploads what you asked for,
                and you review it here — with a full record of who sent what, and when.
            </p>
        </div>

        <div class="position-relative" style="font-family:var(--font-mono);font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;color:#5C7B98;">
            Staff access only
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-form">
            <span class="eyebrow">Console</span>
            <h1 class="mb-1">Sign in</h1>
            <p class="text-muted-2 mb-4" style="font-size:.9rem;">Use the email address your agency registered.</p>

            @include('layouts.partials.flash')

            <form method="POST" action="{{ route('admin.login') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="username" autofocus
                           value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="position-relative">
                        <input id="password" name="password" type="password" autocomplete="current-password"
                               class="form-control @error('password') is-invalid @enderror" style="padding-right:2.8rem;">
                        <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-1 text-muted-2"
                                data-toggle-password="#password" aria-label="Show password">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label" for="remember" style="font-size:.86rem;">Keep me signed in</label>
                </div>

                <button type="submit" class="btn btn-ink w-100">Sign in</button>
            </form>

            <p class="form-hint mt-4 mb-0">
                Candidates do not sign in here — they use the link emailed to them.
            </p>
        </div>
    </section>
</div>

@include('layouts.partials.scripts')
<script src="{{ asset('assets/js/admin.js') }}"></script>
</body>
</html>
