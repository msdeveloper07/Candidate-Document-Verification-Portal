@extends('layouts.plain')

@section('title', 'Enter your code')

@section('content')
<div class="centre-card">
    <h1>Enter your code</h1>
    <p class="lede">
        We sent {{ $codeLength }} digits to <span class="mono">{{ $candidate->masked_phone }}</span>.
        It can take a minute to arrive.
    </p>

    @if ($errors->has('phone'))
        <div class="notice notice-warn mb-3" style="font-size:.86rem;">
            <div>{{ $errors->first('phone') }}</div>
        </div>
    @endif

    @if (session('dev_otp'))
        <div class="notice notice-info mb-3" style="font-size:.86rem;">
            <div><b>Development mode.</b> Your code is <span class="mono">{{ session('dev_otp') }}</span></div>
        </div>
    @endif

    <form method="POST" action="{{ route('candidate.verify-otp') }}" novalidate data-otp-form>
        @csrf

        <div class="otp-grid" data-otp-inputs>
            @for ($i = 0; $i < $codeLength; $i++)
                <input type="text" inputmode="numeric" maxlength="1" name="digits[]"
                       class="otp-box" autocomplete="one-time-code"
                       aria-label="Digit {{ $i + 1 }}" @if ($i === 0) autofocus @endif>
            @endfor
        </div>

        @error('code')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
        @error('digits')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror

        <button type="submit" class="btn-finish">Verify and continue</button>
    </form>

    <div class="d-flex justify-content-between align-items-center mt-3 gap-2 flex-wrap">
        <form method="POST" action="{{ route('candidate.resend-otp') }}">
            @csrf
            <button type="submit" class="btn btn-quiet btn-sm" data-resend-button
                    data-wait="{{ $resendIn ?? 0 }}">
                Send it again
            </button>
        </form>

        <a href="{{ route('candidate.verify-phone') }}" class="btn btn-quiet btn-sm">Change number</a>
    </div>
</div>

<p class="form-hint text-center mt-3">
    Check your messages app, including anything filtered as spam.
</p>
@endsection
