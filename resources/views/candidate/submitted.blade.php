@extends('layouts.candidate')

@section('title', 'File sent')

@section('content')
<div class="centre-card text-center" style="margin-top:1rem;">
    <div class="seal">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
            <path d="M20 6 9 17l-5-5"/>
        </svg>
    </div>

    <h1>Your file is with us</h1>
    <p class="lede">
        Thank you, {{ $candidate->first_name }}. Reference
        <span class="mono">{{ $candidate->reference_no }}</span>.
    </p>

    <div class="instructions text-start">
        <span class="eyebrow">What happens now</span>
        A recruiter checks each document. You’ll get an email as each one is decided —
        if anything needs a new copy, that email will say exactly what to change and
        this page will reopen for you.
    </div>

    <a href="{{ route('candidate.dashboard') }}" class="btn btn-quiet">Review what I sent</a>
</div>
@endsection
