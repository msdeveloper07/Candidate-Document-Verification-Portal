@extends('layouts.admin')
@section('title', 'Agency settings')

@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">Configuration</span>
        <h1>Agency settings</h1>
        <p class="lede">Your name and contact details, and the two timing rules that govern
            how long a candidate has to act.</p>
    </div>
</div>

@include('layouts.partials.flash')

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card-flat mb-3">
                <div class="card-head">
                    <div>
                        <span class="eyebrow">Branding</span>
                        <h2 style="font-size:1rem;">How candidates see you</h2>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="agency_name">Agency name</label>
                        <input id="agency_name" name="agency_name"
                               class="form-control @error('agency_name') is-invalid @enderror"
                               value="{{ old('agency_name', $settings['agency_name'] ?? config('portal.agency_name')) }}" required>
                        <div class="form-hint">Shown on every page, in the sidebar, and in the subject line of every email.</div>
                        @error('agency_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="agency_tagline">Tagline</label>
                        <input id="agency_tagline" name="agency_tagline" class="form-control"
                               value="{{ old('agency_tagline', $settings['agency_tagline'] ?? '') }}">
                        <div class="form-hint">A short line under your name. Leave blank to hide it.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-7">
                            <label class="form-label" for="support_email">Support email</label>
                            <input id="support_email" name="support_email" type="email"
                                   class="form-control @error('support_email') is-invalid @enderror"
                                   value="{{ old('support_email', $settings['support_email'] ?? '') }}">
                            <div class="form-hint">Offered to candidates who get stuck or whose link expired.</div>
                            @error('support_email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-5">
                            <label class="form-label" for="support_phone">Support phone</label>
                            <input id="support_phone" name="support_phone" class="form-control mono"
                                   value="{{ old('support_phone', $settings['support_phone'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-flat">
                <div class="card-head">
                    <div>
                        <span class="eyebrow">Workflow</span>
                        <h2 style="font-size:1rem;">Timing</h2>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="invite_valid_days">Upload link stays live</label>
                            <div class="d-flex align-items-center gap-2">
                                <input id="invite_valid_days" name="invite_valid_days" type="number" min="1" max="90"
                                       class="form-control mono @error('invite_valid_days') is-invalid @enderror"
                                       style="max-width:110px;"
                                       value="{{ old('invite_valid_days', $settings['invite_valid_days'] ?? config('portal.invite.valid_days')) }}" required>
                                <span class="text-muted-2">days</span>
                            </div>
                            <div class="form-hint">Re-sending a link always restarts this clock and kills the old one.</div>
                            @error('invite_valid_days')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="otp_ttl_minutes">Verification code expires after</label>
                            <div class="d-flex align-items-center gap-2">
                                <input id="otp_ttl_minutes" name="otp_ttl_minutes" type="number" min="2" max="60"
                                       class="form-control mono @error('otp_ttl_minutes') is-invalid @enderror"
                                       style="max-width:110px;"
                                       value="{{ old('otp_ttl_minutes', $settings['otp_ttl_minutes'] ?? config('portal.otp.ttl_minutes')) }}" required>
                                <span class="text-muted-2">minutes</span>
                            </div>
                            @error('otp_ttl_minutes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-ink">Save settings</button>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-flat">
                <div class="card-head">
                    <div>
                        <span class="eyebrow">Note</span>
                        <h2 style="font-size:1rem;">These override the .env file</h2>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted-2" style="font-size:.87rem;">
                        Whatever you save here wins over <span class="mono">AGENCY_NAME</span> and the
                        matching values in <span class="mono">.env</span>. That way the name can be changed
                        from this screen without anyone touching the server.
                    </p>
                    <p class="text-muted-2 mb-0" style="font-size:.87rem;">
                        Changing the wording of the emails themselves is separate —
                        that lives under <a href="{{ route('admin.email-templates.index') }}">Email templates</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
