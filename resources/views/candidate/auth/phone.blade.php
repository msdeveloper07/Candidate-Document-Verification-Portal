@extends('layouts.plain')

@section('title', 'Confirm your number')

@section('content')
<div class="centre-card">
    <h1>Let’s check it’s you</h1>
    <p class="lede">
        Hello {{ $candidate->first_name }}. Your documents are private, so we send a
        code to the mobile number {{ config('portal.agency_name') }} has on file.
    </p>

    <div class="instructions mb-3">
        <span class="eyebrow">The number we hold</span>
        Ends in <span class="mono">{{ $hint }}</span> — enter it in full below.
    </div>

    <form method="POST" action="{{ route('candidate.send-otp') }}" novalidate>
        @csrf

        <label class="form-label" for="phone">Mobile number</label>
        <div class="phone-group">
            <select name="dial_code" class="form-select" style="max-width:120px;" aria-label="Country code">
                @foreach (config('countries') as $country)
                    <option value="{{ $country['dial'] }}"
                            @selected(old('dial_code', $candidate->dial_code) === $country['dial'])>
                        {{ $country['dial'] }} {{ $country['code'] }}
                    </option>
                @endforeach
            </select>
            <input id="phone" name="phone" inputmode="numeric" autocomplete="tel-national" autofocus
                   class="form-control mono @error('phone') is-invalid @enderror"
                   value="{{ old('phone') }}" placeholder="Without the country code" required>
        </div>

        @error('phone')<div class="invalid-feedback d-block mt-2">{{ $message }}</div>@enderror
        @error('dial_code')<div class="invalid-feedback d-block mt-2">{{ $message }}</div>@enderror

        <button type="submit" class="btn-finish mt-4">Send my code</button>
    </form>

    <div class="trust">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <div>
            Nothing is uploaded until you are signed in, and only the team handling your
            application can see your files.
        </div>
    </div>
</div>

<p class="form-hint text-center mt-3">
    Wrong number? Reply to the email from {{ config('portal.agency_name') }} and they will update it.
</p>
@endsection
