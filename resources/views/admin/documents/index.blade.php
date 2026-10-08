@extends('layouts.admin')

@section('title', 'Review queue')
@section('crumb', 'Intake / Documents')

@section('content')

<div class="stat-grid mb-3">
    <div class="stat-tile is-warn"><span class="rule"></span>
        <div class="label">Awaiting review</div><div class="value">{{ $stats['pending'] }}</div>
    </div>
    <div class="stat-tile is-success"><span class="rule"></span>
        <div class="label">Approved</div><div class="value">{{ $stats['approved'] }}</div>
    </div>
    <div class="stat-tile is-danger"><span class="rule"></span>
        <div class="label">Sent back</div><div class="value">{{ $stats['rejected'] }}</div>
    </div>
</div>

<form method="GET" action="{{ route('admin.documents.index') }}" class="filter-bar">
    <div class="field grow">
        <label class="form-label" for="search">Candidate</label>
        <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}"
               class="form-control" placeholder="Name, email or reference">
    </div>

    <div class="field">
        <label class="form-label" for="type">Document</label>
        <select id="type" name="type" class="form-select">
            <option value="">Any document</option>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" @selected((string) ($filters['type'] ?? '') === (string) $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-select">
            <option value="">Awaiting review (default)</option>
            <option value="uploaded"     @selected(($filters['status'] ?? '') === 'uploaded')>Uploaded</option>
            <option value="under_review" @selected(($filters['status'] ?? '') === 'under_review')>Under review</option>
            <option value="approved"     @selected(($filters['status'] ?? '') === 'approved')>Approved</option>
            <option value="rejected"     @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
        </select>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-ink">Apply</button>
        @if (array_filter($filters))
            <a href="{{ route('admin.documents.index') }}" class="btn btn-quiet">Clear</a>
        @endif
    </div>
</form>

<div class="table-shell">
    @if ($documents->isEmpty())
        <div class="empty-state">
            <div class="mark">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <h3>Queue is clear</h3>
            <p>Every uploaded document has been reviewed. New uploads land here automatically.</p>
        </div>
    @else
        @foreach ($documents as $doc)
            <div class="doc-row">
                <div class="doc-thumb">
                    @if ($doc->isImage())
                        <img src="{{ route('admin.documents.preview', $doc) }}" alt="">
                    @else
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                    @endif
                </div>

                <div class="flex-grow-1 min-width-0">
                    <div class="cell-name">{{ $doc->documentType->name }}</div>
                    <div class="cell-sub">
                        <a href="{{ route('admin.candidates.show', $doc->candidate) }}">{{ $doc->candidate->full_name }}</a>
                        · <span class="mono">{{ $doc->candidate->reference_no }}</span>
                    </div>
                    <div class="cell-ref">
                        {{ $doc->original_name }} · {{ $doc->size_label }} · {{ $doc->created_at->diffForHumans() }}
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <x-stamp :tone="$doc->status->tone()" :label="$doc->status->label()" />
                    <a href="{{ route('admin.candidates.show', $doc->candidate) }}" class="btn btn-quiet btn-sm">Open file</a>
                </div>
            </div>
        @endforeach
    @endif
</div>

<div class="mt-3">{{ $documents->links() }}</div>
@endsection
