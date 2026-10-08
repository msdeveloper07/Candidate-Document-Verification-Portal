@extends('layouts.plain')

@section('title', 'Signed out')

@section('content')
<div class="centre-card text-center">
    <div class="seal">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
    </div>

    <h1>You’re signed out</h1>
    <p class="lede">
        Everything you sent is saved. Open your upload link again whenever you want to
        carry on — you’ll get a fresh code by text.
    </p>
</div>
@endsection
