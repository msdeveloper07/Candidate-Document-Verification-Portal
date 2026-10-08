<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
    <link href="{{ asset('assets/css/candidate.css') }}" rel="stylesheet">
</head>
<body class="portal-body">

<header class="portal-head">
    <div class="portal-shell d-flex align-items-center justify-content-between gap-3">
        <div class="portal-mark">
            <span class="seal">{{ mb_substr(config('portal.agency_name'), 0, 1) }}</span>
            <div class="min-width-0">
                <div class="agency">{{ config('portal.agency_name') }}</div>
                <div class="kind">Credentials</div>
            </div>
        </div>

        @auth('candidate')
            <form method="POST" action="{{ route('candidate.logout') }}">
                @csrf
                <button type="submit" class="btn btn-quiet btn-sm">Sign out</button>
            </form>
        @endauth
    </div>
</header>

<main class="portal-shell portal-stack">
    @include('layouts.partials.flash')
    @yield('content')
</main>

@yield('actionbar')

@include('layouts.partials.scripts')
<script src="{{ asset('assets/js/candidate.js') }}"></script>
@stack('scripts')
</body>
</html>
