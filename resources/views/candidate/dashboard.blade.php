@extends('layouts.candidate')

@section('title', 'Your documents')

@section('content')

@include('candidate.partials.clearance')

{{-- The single next action, so nobody has to scan the list to find it --}}
@php
    $next = collect($checklist)->first(fn ($i) =>
        ! $i['document'] || $i['document']->status === \App\Enums\DocumentStatus::Rejected
    );
@endphp

@if ($next)
    <button type="button" class="nextup" data-jump-to="req-{{ $next['type']->id }}">
        <div class="min-width-0">
            <div class="lead">{{ $next['document'] ? 'Needs a new copy' : 'Next up' }}</div>
            <div class="what">{{ $next['type']->name }}</div>
            <div class="why">{{ $next['document'] ? 'The reviewer left a note' : $next['type']->description }}</div>
        </div>
        <svg class="arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </button>
@endif

@if ($errors->has('submit'))
    <div class="notice notice-danger mt-3">{{ $errors->first('submit') }}</div>
@endif

{{-- ---------- Dossier ---------- --}}
<div class="dossier">
    @foreach ($checklist as $item)
        @php($type = $item['type'])
        @php($doc  = $item['document'])
        @php($state = $doc?->status->value ?? 'empty')

        <section class="sleeve is-{{ $state === 'empty' ? 'empty' : $state }} {{ $state === 'rejected' || ! $doc ? 'open' : '' }}"
                 id="req-{{ $type->id }}" data-sleeve data-type-id="{{ $type->id }}">

            <header class="sleeve-head" data-sleeve-toggle>
                <span class="idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>

                <div class="flex-grow-1 min-width-0">
                    <div class="title">{{ $type->name }}</div>
                    <div class="desc">{{ $type->description }}</div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span data-sleeve-stamp>
                        @if ($doc)
                            <x-stamp :tone="$doc->status->tone()" :label="$doc->status->label()" />
                        @else
                            <x-stamp tone="neutral" label="Not sent" />
                        @endif
                    </span>
                    <svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </div>
            </header>

            <div class="sleeve-body" {{ ($state === 'rejected' || ! $doc) ? '' : 'hidden' }}>

                @if ($doc && filled($doc->remarks))
                    <div class="reviewer-note {{ $doc->status === \App\Enums\DocumentStatus::Rejected ? '' : 'is-neutral' }}">
                        <span class="eyebrow" style="color:inherit;opacity:.75;">
                            {{ $doc->status === \App\Enums\DocumentStatus::Rejected ? 'What to fix' : 'Note from the recruiter' }}
                        </span>
                        {{ $doc->remarks }}
                    </div>
                @endif

                <div class="accepts mono">
                    Accepted: {{ $type->extension_list }} · max {{ $type->max_size_label }}
                </div>

                @if ($type->instructions)
                    <div class="instructions">
                        <span class="eyebrow">Before you upload</span>
                        {{ $type->instructions }}
                    </div>
                @endif

                {{-- Current file --}}
                <div data-current-file class="{{ $doc ? '' : 'd-none' }} mb-3">
                    @if ($doc)
                        <div class="file-row">
                            <span class="glyph">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                            </span>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fname" data-file-name>{{ $doc->original_name }}</div>
                                <div class="fmeta" data-file-meta>
                                    {{ $doc->size_label }} · uploaded {{ $doc->updated_at->format('d M Y') }} at {{ $doc->updated_at->format('H:i') }}
                                </div>
                                <div class="fmeta" data-file-state>{{ $doc->status->candidateHint() }}</div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <a href="{{ route('candidate.documents.preview', $doc) }}" target="_blank" rel="noopener"
                                   class="btn btn-quiet btn-sm">View</a>
                                <a href="{{ route('candidate.documents.download', $doc) }}"
                                   class="btn btn-quiet btn-sm">Save</a>
                                @if ($doc->status !== \App\Enums\DocumentStatus::Approved)
                                    <button type="button" class="btn btn-quiet btn-sm" data-replace-trigger>Replace</button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Upload --}}
                @if (! $doc || $doc->status->value !== 'approved')
                    <form data-upload-form
                          action="{{ route('candidate.documents.upload') }}"
                          data-type-id="{{ $type->id }}"
                          data-accept="{{ collect($type->allowed_extensions)->map(fn($e) => '.'.$e)->implode(',') }}"
                          data-max-kb="{{ $type->max_size_kb }}">
                        @csrf
                        <input type="hidden" name="document_type_id" value="{{ $type->id }}">

                        <label class="dropzone" data-dropzone tabindex="0" role="button">
                            <input type="file" name="file"
                                   accept="{{ collect($type->allowed_extensions)->map(fn($e) => '.'.$e)->implode(',') }}"
                                   data-file-input>
                            <div class="icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 9 5-5 5 5"/><path d="M12 4v12"/>
                                </svg>
                            </div>
                            <div class="primary">
                                {{ $doc ? 'Choose a replacement file' : 'Tap to choose a file' }}
                            </div>
                            <div class="secondary">{{ $type->extension_list }} · up to {{ $type->max_size_label }}</div>
                        </label>

                        <div class="upload-progress d-none" data-progress-track>
                            <div class="fill" data-progress-fill></div>
                        </div>

                        <div class="notice notice-danger mt-2 d-none" data-upload-error></div>
                    </form>
                @else
                    <p class="text-muted-2 mb-0" style="font-size:.86rem;">
                        Approved on {{ $doc->reviewed_at?->format('d M Y') }}. Nothing more to do here.
                    </p>
                @endif
            </div>
        </section>
    @endforeach
</div>

{{-- ---------- References ---------- --}}
<section class="sleeve {{ $candidate->completedReferenceCount() ? 'is-approved' : '' }} open mt-3" id="references">
    <header class="sleeve-head">
        <span class="idx">R</span>
        <div class="flex-grow-1 min-width-0">
            <div class="title">Professional references <span class="optional">Optional</span></div>
            <div class="desc">Up to three people who have supervised your clinical work</div>
        </div>
        <span>
            @if ($candidate->completedReferenceCount())
                <x-stamp tone="success" label="{{ $candidate->completedReferenceCount() }} added" />
            @else
                <x-stamp tone="neutral" label="None yet" />
            @endif
        </span>
    </header>

    <div class="sleeve-body">
        <div class="instructions">
            <span class="eyebrow">Before you start</span>
            These are not required to send your file, but they speed things up a lot.
            A direct supervisor is best — a charge nurse, unit manager or agency coordinator.
            We only make contact after speaking with you first.
        </div>

        @if (session('status'))
            <div class="notice notice-success mb-3" style="font-size:.86rem;">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('candidate.references.save') }}">
            @csrf

            @foreach ($candidate->referenceSlots() as $ref)
                @php($slot = $ref->slot)
                <fieldset class="ref-block">
                    <legend>Reference {{ $slot }}</legend>

                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label class="form-label" for="ref{{ $slot }}-name">Full name</label>
                            <input id="ref{{ $slot }}-name" class="form-control @error("references.$slot.name") is-invalid @enderror"
                                   name="references[{{ $slot }}][name]"
                                   value="{{ old("references.$slot.name", $ref->name) }}">
                            @error("references.$slot.name")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="ref{{ $slot }}-rel">How you know them</label>
                            <input id="ref{{ $slot }}-rel" class="form-control @error("references.$slot.relationship") is-invalid @enderror"
                                   name="references[{{ $slot }}][relationship]"
                                   value="{{ old("references.$slot.relationship", $ref->relationship) }}"
                                   placeholder="e.g. Charge Nurse, my direct supervisor">
                            @error("references.$slot.relationship")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="ref{{ $slot }}-org">Facility or organisation</label>
                            <input id="ref{{ $slot }}-org" class="form-control"
                                   name="references[{{ $slot }}][organisation]"
                                   value="{{ old("references.$slot.organisation", $ref->organisation) }}">
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="ref{{ $slot }}-email">Email</label>
                            <input id="ref{{ $slot }}-email" type="email"
                                   class="form-control @error("references.$slot.email") is-invalid @enderror"
                                   name="references[{{ $slot }}][email]"
                                   value="{{ old("references.$slot.email", $ref->email) }}">
                            @error("references.$slot.email")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="ref{{ $slot }}-phone">Phone</label>
                            <div class="phone-group">
                                <input class="form-control mono" style="max-width:88px;"
                                       name="references[{{ $slot }}][dial_code]"
                                       value="{{ old("references.$slot.dial_code", $ref->dial_code ?: '+1') }}">
                                <input id="ref{{ $slot }}-phone" class="form-control mono" inputmode="numeric"
                                       name="references[{{ $slot }}][phone]"
                                       value="{{ old("references.$slot.phone", $ref->phone) }}">
                            </div>
                            @error("references.$slot.phone")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </fieldset>
            @endforeach

            <button type="submit" class="btn btn-ink w-100 mt-2">Save references</button>
            <p class="form-hint text-center mt-2 mb-0">
                Leave any of them blank. If you do fill one in, we need a name and either
                an email or a phone number.
            </p>
        </form>
    </div>
</section>

{{-- ---------- Finish ---------- --}}
<div class="finish" id="finish">
    <h2>{{ $canSubmit ? 'Ready to send' : 'Not ready yet' }}</h2>
    <p>
        @if ($canSubmit)
            Hand your file to {{ config('portal.agency_name') }}. You can still replace something
            afterwards if they ask.
        @else
            <span data-submit-hint>Every document must be uploaded first.</span>
        @endif
    </p>

    <form method="POST" action="{{ route('candidate.submit') }}">
        @csrf
        <button type="submit" class="btn-finish" data-submit-button {{ $canSubmit ? '' : 'disabled' }}>
            Send my file
        </button>
    </form>
</div>

<div class="trust">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    <div>
        Your files are stored privately and are only visible to the team handling your application.
        @if ($support = \App\Models\Setting::config('support_email'))
            Stuck? Email <a href="mailto:{{ $support }}">{{ $support }}</a>.
        @endif
    </div>
</div>
@endsection

{{-- Always-visible progress and action, so the answer is never scrolled away --}}
@section('actionbar')
    <div class="actionbar">
        <div class="inner">
            <div class="status">
                <div class="big">
                    <span data-bar-done>{{ $summary['approved'] }}</span>
                    of {{ $summary['line_items'] }} cleared
                </div>
                <div class="small" data-bar-hint>
                    @if ($canSubmit)
                        Ready to send
                    @elseif ($summary['rejected'] > 0)
                        {{ $summary['rejected'] }} to replace
                    @else
                        {{ $summary['missing'] }} left to do
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('candidate.submit') }}">
                @csrf
                <button type="submit" class="btn-finish" data-submit-button {{ $canSubmit ? '' : 'disabled' }}>
                    Send
                </button>
            </form>
        </div>
    </div>
@endsection
