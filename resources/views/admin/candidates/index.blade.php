@extends('layouts.admin')

@section('title', 'Candidates')
@section('crumb', 'Intake')

@section('actions')
    <a href="{{ route('admin.candidates.create') }}" class="btn btn-ink btn-sm">Add candidate</a>
@endsection

@section('content')

<form method="GET" action="{{ route('admin.candidates.index') }}" class="filter-bar">
    <div class="field grow">
        <label class="form-label" for="search">Search</label>
        <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}"
               class="form-control" placeholder="Name, email, mobile or reference">
    </div>

    <div class="field">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-select">
            <option value="">Any status</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label class="form-label" for="recruiter">Invited by</label>
        <select id="recruiter" name="recruiter" class="form-select">
            <option value="">Anyone</option>
            @foreach ($recruiters as $recruiter)
                <option value="{{ $recruiter->id }}" @selected((string) ($filters['recruiter'] ?? '') === (string) $recruiter->id)>{{ $recruiter->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-ink">Apply</button>
        @if (array_filter($filters))
            <a href="{{ route('admin.candidates.index') }}" class="btn btn-quiet">Clear</a>
        @endif
    </div>
</form>

<div class="table-shell">
    @if ($candidates->isEmpty())
        <div class="empty-state">
            <div class="mark">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
            <h3>No candidates match</h3>
            <p>Try a different search, or add someone new to start collecting documents.</p>
            <a href="{{ route('admin.candidates.create') }}" class="btn btn-ink">Add candidate</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table-clean">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Position</th>
                        <th style="width:170px;">Collection</th>
                        <th>Status</th>
                        <th>Invited</th>
                        <th style="width:1%;"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($candidates as $candidate)
                    @php($summary = $candidate->collectionSummary())
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar" style="width:34px;height:34px;font-size:.72rem;">{{ $candidate->initials }}</span>
                                <div>
                                    <a href="{{ route('admin.candidates.show', $candidate) }}" class="cell-name text-decoration-none">{{ $candidate->full_name }}</a>
                                    <div class="cell-sub">{{ $candidate->email }}</div>
                                    <div class="cell-ref">{{ $candidate->reference_no }} · {{ $candidate->full_phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:.86rem;">{{ $candidate->position_applied ?: '—' }}</div>
                            <div class="cell-sub">{{ $candidate->country_name ?: 'Country not set' }}</div>
                        </td>
                        <td>
                            <x-collection-bar :summary="$summary" />
                            <div class="cell-sub mono mt-1">{{ $summary['approved'] }}/{{ $summary['total'] }} approved</div>
                        </td>
                        <td><x-stamp :tone="$candidate->status->tone()" :label="$candidate->status->label()" /></td>
                        <td>
                            <div style="font-size:.84rem;">{{ $candidate->created_at->format('d M Y') }}</div>
                            <div class="cell-sub">by {{ $candidate->invitedBy?->name ?? 'System' }}</div>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-quiet btn-sm" data-bs-toggle="dropdown" aria-label="Actions">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item" href="{{ route('admin.candidates.show', $candidate) }}">Open file</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.candidates.edit', $candidate) }}">Edit details</a></li>
                                    @if ($candidate->inviteIsValid())
                                        <li>
                                            <button type="button" class="dropdown-item"
                                                    data-copy-value="{{ $candidate->invite_url }}">
                                                Copy upload link
                                            </button>
                                        </li>
                                    @endif
                                    <li>
                                        <form method="POST" action="{{ route('admin.candidates.resend-invite', $candidate) }}">
                                            @csrf
                                            <button class="dropdown-item" type="submit">Re-send upload link</button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('admin.candidates.destroy', $candidate) }}"
                                              data-confirm="Archive {{ $candidate->full_name }}? Their upload link stops working.">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger" type="submit">Archive</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="collection-legend">
    <span><i style="background:var(--success)"></i>Approved</span>
    <span><i style="background:var(--warn)"></i>In review</span>
    <span><i style="background:var(--danger)"></i>Re-upload asked</span>
    <span><i style="background:var(--line-strong)"></i>Not sent yet</span>
</div>

<div class="mt-3">{{ $candidates->links() }}</div>
@endsection
