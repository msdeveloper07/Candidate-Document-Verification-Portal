@extends('layouts.admin')

@section('title', 'Overview')
@section('crumb', 'Console')

@section('actions')
    <a href="{{ route('admin.candidates.create') }}" class="btn btn-ink btn-sm">Add candidate</a>
@endsection

@section('content')
@php($peak = max(collect($intake)->max('value'), 1))

<div class="stat-grid mb-4">
    <div class="stat-tile is-info">
        <span class="rule"></span>
        <div class="label">Candidates</div>
        <div class="value">{{ $counts['total'] }}</div>
        <div class="foot">{{ $counts['in_progress'] }} currently uploading</div>
    </div>

    <div class="stat-tile is-warn">
        <span class="rule"></span>
        <div class="label">Files awaiting review</div>
        <div class="value">{{ $documentStats['pending'] }}</div>
        <div class="foot">Oldest first in the queue</div>
    </div>

    <div class="stat-tile is-success">
        <span class="rule"></span>
        <div class="label">Fully approved</div>
        <div class="value">{{ $counts['approved'] }}</div>
        <div class="foot">Complete document sets</div>
    </div>

    <div class="stat-tile is-danger">
        <span class="rule"></span>
        <div class="label">Waiting on the candidate</div>
        <div class="value">{{ $counts['rejected'] }}</div>
        <div class="foot">Re-uploads requested</div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-flat h-100">
            <div class="card-head">
                <div>
                    <span class="eyebrow">Newest first</span>
                    <h2 style="font-size:1.05rem;">Recent candidates</h2>
                </div>
                <a href="{{ route('admin.candidates.index') }}" class="btn btn-quiet btn-sm">See all</a>
            </div>

            @if ($recent->isEmpty())
                <div class="empty-state">
                    <div class="mark">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                    </div>
                    <h3>No candidates yet</h3>
                    <p>Add your first candidate and we will email them a link to upload their documents.</p>
                    <a href="{{ route('admin.candidates.create') }}" class="btn btn-ink">Add candidate</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-clean">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th style="width:150px;">Collection</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($recent as $candidate)
                            @php($summary = $candidate->collectionSummary())
                            <tr onclick="window.location='{{ route('admin.candidates.show', $candidate) }}'" style="cursor:pointer;">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar" style="width:32px;height:32px;font-size:.7rem;">{{ $candidate->initials }}</span>
                                        <div>
                                            <div class="cell-name">{{ $candidate->full_name }}</div>
                                            <div class="cell-ref">{{ $candidate->reference_no }} · {{ $candidate->country_name ?: 'Country not set' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <x-collection-bar :summary="$summary" />
                                    <div class="cell-sub mono mt-1">{{ $summary['approved'] }}/{{ $summary['total'] }} approved</div>
                                </td>
                                <td><x-stamp :tone="$candidate->status->tone()" :label="$candidate->status->label()" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-flat mb-3">
            <div class="card-head">
                <div>
                    <span class="eyebrow">Last 8 weeks</span>
                    <h2 style="font-size:1.05rem;">Candidates invited</h2>
                </div>
            </div>
            <div class="card-body">
                <div class="intake-chart">
                    @foreach ($intake as $week)
                        <div class="col" title="{{ $week['value'] }} invited week of {{ $week['label'] }}">
                            <div class="bar" style="height:{{ max(round(($week['value'] / $peak) * 92), 3) }}px"></div>
                            <span class="tick">{{ $week['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card-flat">
            <div class="card-head">
                <div>
                    <span class="eyebrow">Audit trail</span>
                    <h2 style="font-size:1.05rem;">Latest activity</h2>
                </div>
            </div>
            <div class="card-body">
                @if ($activity->isEmpty())
                    <p class="text-muted-2 mb-0" style="font-size:.88rem;">Nothing has happened yet.</p>
                @else
                    <div class="timeline">
                        @foreach ($activity as $entry)
                            <div class="timeline-item {{ str_starts_with($entry->event, 'document.') ? 'accent' : '' }}">
                                <div class="what">{{ $entry->description }}</div>
                                <div class="when">
                                    {{ $entry->created_at?->diffForHumans() }}
                                    @if ($entry->candidate)
                                        · {{ $entry->candidate->full_name }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if ($awaiting->total() > 0)
    <div class="card-flat mt-3">
        <div class="card-head">
            <div>
                <span class="eyebrow">Oldest first</span>
                <h2 style="font-size:1.05rem;">Waiting for your review</h2>
            </div>
            <a href="{{ route('admin.documents.index') }}" class="btn btn-brass btn-sm">Open queue</a>
        </div>

        @foreach ($awaiting as $doc)
            <div class="doc-row">
                <div class="doc-thumb">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                </div>
                <div class="flex-grow-1 min-width-0">
                    <div class="cell-name">{{ $doc->documentType->name }}</div>
                    <div class="cell-sub">
                        {{ $doc->candidate->full_name }} ·
                        <span class="mono">{{ $doc->candidate->reference_no }}</span> ·
                        uploaded {{ $doc->created_at->diffForHumans() }}
                    </div>
                </div>
                <a href="{{ route('admin.candidates.show', $doc->candidate) }}" class="btn btn-quiet btn-sm">Review</a>
            </div>
        @endforeach
    </div>
@endif
@endsection
