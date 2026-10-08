@extends('layouts.plain')

@section('title', 'Link no longer works')

@section('content')
<div class="centre-card text-center">
    <div class="seal" style="background:var(--warn-soft);border-color:var(--warn);color:var(--warn);">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
        </svg>
    </div>

    <h1>This link has expired</h1>
    <p class="lede">
        Upload links stay live for {{ config('portal.invite.valid_days') }} days. Ask your recruiter
        for a fresh one — anything you already uploaded is safe and still on file.
    </p>

    @if ($support = \App\Models\Setting::config('support_email'))
        <a href="mailto:{{ $support }}?subject=New%20upload%20link%20please" class="btn-finish d-inline-flex align-items-center justify-content-center px-4" style="width:auto;">
            Ask for a new link
        </a>
    @endif
</div>
@endsection
