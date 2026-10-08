<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
    <link href="{{ asset('assets/css/candidate.css') }}" rel="stylesheet">
</head>
<body class="portal-body">

<div class="centre-page">
    <div class="portal-mark mb-4">
        <span class="seal">{{ mb_substr(config('portal.agency_name'), 0, 1) }}</span>
        <div class="min-width-0">
            <div class="agency">{{ config('portal.agency_name') }}</div>
            <div class="kind">Credentials</div>
        </div>
    </div>

    @include('layouts.partials.flash')
    @yield('content')
</div>

@include('layouts.partials.scripts')
<script src="{{ asset('assets/js/candidate.js') }}"></script>
@stack('scripts')
</body>
</html>
