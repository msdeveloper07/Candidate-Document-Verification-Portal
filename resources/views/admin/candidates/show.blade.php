@extends('layouts.admin')

@section('title', $candidate->full_name)
@section('crumb', 'Intake / Candidates')

@section('actions')
    <a href="{{ route('admin.candidates.edit', $candidate) }}" class="btn btn-quiet btn-sm">Edit</a>
    <form method="POST" action="{{ route('admin.candidates.resend-invite', $candidate) }}" class="d-inline">
        @csrf
        <button class="btn btn-ink btn-sm" type="submit">Re-send link</button>
    </form>
@endsection

@section('content')
@php($documents = $candidate->documents->keyBy('document_type_id'))

<div class="row g-3">
    {{-- ---------- Left: identity + status ---------- --}}
    <div class="col-lg-4">
        <div class="card-flat mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="avatar avatar-lg">{{ $candidate->initials }}</span>
                    <div class="min-width-0">
                        <h2 style="font-size:1.12rem;">{{ $candidate->full_name }}</h2>
                        <div class="mono text-faint" style="font-size:.75rem;">{{ $candidate->reference_no }}</div>
                    </div>
                </div>

                <x-stamp :tone="$candidate->status->tone()" :label="$candidate->status->label()" class="mb-3" />

                <dl class="mb-0" style="font-size:.86rem;">
                    <dt class="text-faint fw-normal" style="font-size:.76rem;">Email</dt>
                    <dd class="mb-2"><a href="mailto:{{ $candidate->email }}">{{ $candidate->email }}</a></dd>

                    <dt class="text-faint fw-normal" style="font-size:.76rem;">Mobile</dt>
                    <dd class="mb-2 mono">{{ $candidate->full_phone }}</dd>

                    <dt class="text-faint fw-normal" style="font-size:.76rem;">Date of birth</dt>
                    <dd class="mb-2 mono">
                        {{ $candidate->date_of_birth?->format('d M Y') ?: 'Not set' }}
                        @if ($candidate->date_of_birth)
                            <span class="text-faint">· {{ $candidate->date_of_birth->age }} yrs</span>
                        @endif
                    </dd>

                    <dt class="text-faint fw-normal" style="font-size:.76rem;">Address</dt>
                    <dd class="mb-2">
                        @php($addr = array_filter([
                            $candidate->address_line1, $candidate->address_line2,
                            trim(implode(' ', array_filter([$candidate->city, $candidate->state]))),
                            $candidate->postal_code, $candidate->country_name,
                        ]))
                        {{ $addr ? implode(', ', $addr) : 'Not set' }}
                    </dd>

                    <dt class="text-faint fw-normal" style="font-size:.76rem;">Position</dt>
                    <dd class="mb-2">{{ $candidate->position_applied ?: 'Not set' }}</dd>

                    <dt class="text-faint fw-normal" style="font-size:.76rem;">Availability</dt>
                    <dd class="mb-2">
                        {{ $candidate->availability?->label() ?: 'Not stated' }}
                        @if ($candidate->available_from)
                            <span class="text-faint">· from {{ $candidate->available_from->format('d M Y') }}</span>
                        @endif
                    </dd>

                    <dt class="text-faint fw-normal" style="font-size:.76rem;">Preferred shift</dt>
                    <dd class="mb-2">{{ $candidate->preferred_shift?->label() ?: 'Not stated' }}</dd>

                    <dt class="text-faint fw-normal" style="font-size:.76rem;">Invited by</dt>
                    <dd class="mb-0">{{ $candidate->invitedBy?->name ?? 'System' }} on {{ $candidate->created_at->format('d M Y') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Upload link state --}}
        <div class="card-flat mb-3 {{ session('highlight_invite') ? 'is-flagged' : '' }}" id="invite-link">
            <div class="card-head">
                <div>
                    <span class="eyebrow">Access</span>
                    <h2 style="font-size:1rem;">Upload link</h2>
                </div>
                @if ($candidate->invite_sent_at)
                    <span class="text-faint" style="font-size:.74rem;">
                        Emailed {{ $candidate->invite_sent_at->diffForHumans() }}
                    </span>
                @else
                    <x-stamp tone="warn" label="Not emailed" />
                @endif
            </div>
            <div class="card-body">
                @if (session('highlight_invite'))
                    <div class="notice notice-warn mb-3" style="font-size:.84rem;">
                        <div>Send this link to the candidate yourself — by WhatsApp, SMS or from your own inbox.
                        It works exactly the same as the emailed one.</div>
                    </div>
                @endif

                @if ($candidate->inviteIsValid())
                    <p class="mb-2" style="font-size:.85rem;">
                        Live until <b>{{ $candidate->invite_expires_at->format('d M Y') }}</b>
                        <span class="text-muted-2">({{ $candidate->invite_expires_at->diffForHumans() }})</span>
                    </p>

                    <div class="input-group input-group-sm mb-2">
                        <input class="form-control mono" style="font-size:.72rem;" readonly
                               value="{{ $candidate->invite_url }}" data-copy-source>
                        <button class="btn btn-quiet" type="button" data-copy-trigger>Copy</button>
                    </div>

                    <form method="POST" action="{{ route('admin.candidates.revoke-invite', $candidate) }}"
                          data-confirm="Revoke this link? The candidate will not be able to upload until you send a new one.">
                        @csrf
                        <button class="btn btn-danger-quiet btn-sm w-100" type="submit">Revoke link</button>
                    </form>
                @else
                    <div class="notice notice-warn mb-2" style="font-size:.84rem;">
                        <div>No live link. The candidate cannot upload right now.</div>
                    </div>
                    <form method="POST" action="{{ route('admin.candidates.resend-invite', $candidate) }}">
                        @csrf
                        <button class="btn btn-ink btn-sm w-100" type="submit">Send a new link</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Manual status override --}}
        <div class="card-flat mb-3">
            <div class="card-head">
                <div>
                    <span class="eyebrow">Override</span>
                    <h2 style="font-size:1rem;">Set status</h2>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.candidates.status', $candidate) }}">
                    @csrf
                    <select name="status" class="form-select form-select-sm mb-2">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($candidate->status->value === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input name="note" class="form-control form-control-sm mb-2" placeholder="Reason (optional)">
                    <button class="btn btn-quiet btn-sm w-100" type="submit">Save status</button>
                </form>
            </div>
        </div>

        @if ($candidate->internal_notes)
            <div class="card-flat">
                <div class="card-body">
                    <span class="eyebrow">Internal note</span>
                    <p class="mb-0" style="font-size:.86rem;">{{ $candidate->internal_notes }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- ---------- Right: documents ---------- --}}
    <div class="col-lg-8">
        <div class="card-flat mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <span class="eyebrow">Collection</span>
                        <h2 style="font-size:1.05rem;">
                            {{ $summary['approved'] }} of {{ $summary['total'] }} documents approved
                        </h2>
                    </div>
                    <span class="mono" style="font-size:1.5rem;font-weight:500;color:var(--ink);">{{ $summary['percent'] }}%</span>
                </div>

                <x-collection-bar :summary="$summary" />

                <div class="collection-legend">
                    <span><i style="background:var(--success)"></i>{{ $summary['approved'] }} approved</span>
                    <span><i style="background:var(--warn)"></i>{{ $summary['pending'] }} in review</span>
                    <span><i style="background:var(--danger)"></i>{{ $summary['rejected'] }} re-upload asked</span>
                    <span><i style="background:var(--line-strong)"></i>{{ $summary['missing'] }} not sent</span>
                </div>
            </div>
        </div>

        <div class="card-flat mb-3">
            <div class="card-head">
                <div>
                    <span class="eyebrow">Requested from this candidate</span>
                    <h2 style="font-size:1.05rem;">Documents</h2>
                </div>
            </div>

            @forelse ($candidate->requirements as $type)
                @php($doc = $documents->get($type->id))
                <div class="doc-row">
                    <div class="doc-thumb">
                        @if ($doc && $doc->isImage())
                            <img src="{{ route('admin.documents.preview', $doc) }}" alt="">
                        @else
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                        @endif
                    </div>

                    <div class="flex-grow-1 min-width-0">
                        <div class="cell-name">{{ $type->name }}</div>
                        @if ($doc)
                            <div class="cell-sub text-break">{{ $doc->original_name }}</div>
                            <div class="cell-ref">
                                {{ $doc->size_label }} · uploaded {{ $doc->updated_at->format('d M Y, H:i') }}
                                @if ($doc->version > 1) · version {{ $doc->version }} @endif
                            </div>
                            @if ($doc->remarks)
                                <div class="cell-sub mt-1" style="color:var(--danger);">“{{ $doc->remarks }}”</div>
                            @endif
                        @else
                            <div class="cell-sub">Not sent yet</div>
                        @endif
                    </div>

                    <div class="text-end d-flex flex-column align-items-end gap-2">
                        @if ($doc)
                            <x-stamp :tone="$doc->status->tone()" :label="$doc->status->label()" />
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.documents.preview', $doc) }}" target="_blank" rel="noopener"
                                   class="btn btn-quiet btn-sm">View</a>
                                <a href="{{ route('admin.documents.download', $doc) }}" class="btn btn-quiet btn-sm">Download</a>
                                <button type="button" class="btn btn-brass btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#review-{{ $doc->id }}">Review</button>
                            </div>
                        @else
                            <x-stamp tone="neutral" label="Awaiting upload" />
                        @endif
                    </div>
                </div>

            @empty
                <div class="empty-state">
                    <h3>No documents requested</h3>
                    <p>Edit this candidate to choose what they should send.</p>
                    <a href="{{ route('admin.candidates.edit', $candidate) }}" class="btn btn-ink">Choose documents</a>
                </div>
            @endforelse
        </div>

        {{-- Review dialogs live outside the card so nothing clips them. --}}
        @foreach ($candidate->requirements as $type)
            @php($doc = $documents->get($type->id))
            @continue(! $doc)

            <div class="modal fade" id="review-{{ $doc->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content" style="border-radius:var(--r-lg);border:1px solid var(--line);">
                        <div class="modal-header" style="border-bottom:1px solid var(--line);">
                            <div>
                                <span class="eyebrow">Review</span>
                                <h3 style="font-size:1.05rem;">{{ $type->name }}</h3>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3" style="border:1px solid var(--line);border-radius:var(--r-md);overflow:hidden;background:var(--surface-alt);">
                                @if ($doc->isImage())
                                    <img src="{{ route('admin.documents.preview', $doc) }}" alt="{{ $type->name }}"
                                         style="width:100%;max-height:420px;object-fit:contain;display:block;">
                                @elseif ($doc->isPdf())
                                    <embed src="{{ route('admin.documents.preview', $doc) }}" type="application/pdf"
                                           style="width:100%;height:420px;display:block;">
                                @else
                                    <div class="p-4 text-center text-muted-2" style="font-size:.88rem;">
                                        No inline preview for {{ mb_strtoupper($doc->extension) }} files.
                                        <a href="{{ route('admin.documents.download', $doc) }}">Download to view.</a>
                                    </div>
                                @endif
                            </div>

                            <div class="mono text-faint mb-3" style="font-size:.72rem;">
                                {{ $doc->original_name }} · {{ $doc->size_label }} · version {{ $doc->version }}
                                @if ($doc->checksum) · sha256 {{ Str::limit($doc->checksum, 16, '…') }} @endif
                            </div>

                            <form method="POST" action="{{ route('admin.documents.review', $doc) }}" data-review-form>
                                @csrf
                                <input type="hidden" name="decision" value="approve" data-decision>

                                <label class="form-label" for="remarks-{{ $doc->id }}">Note to the candidate</label>
                                <textarea id="remarks-{{ $doc->id }}" name="remarks" rows="3" class="form-control"
                                          placeholder="Required when asking for a re-upload — say exactly what to fix.">{{ $doc->remarks }}</textarea>
                                <div class="form-hint">This text is emailed to the candidate and shown on their upload page.</div>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-ink flex-grow-1" data-decide="approve">Approve</button>
                                    <button type="submit" class="btn btn-danger-quiet flex-grow-1" data-decide="reject">Ask for a re-upload</button>
                                </div>
                            </form>

                            <hr style="border-color:var(--line);">

                            <form method="POST" action="{{ route('admin.documents.destroy', $doc) }}"
                                  data-confirm="Delete this file permanently? The candidate will be asked to upload it again.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-quiet btn-sm w-100">Delete this file</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="card-flat mb-3">
            <div class="card-head">
                <div>
                    <span class="eyebrow">Supplied by the candidate</span>
                    <h2 style="font-size:1.02rem;">Professional references</h2>
                </div>
                <x-stamp :tone="$candidate->completedReferenceCount() ? 'success' : 'neutral'"
                         :label="$candidate->completedReferenceCount()
                            ? $candidate->completedReferenceCount().' supplied'
                            : 'Optional'" />
            </div>
            <div class="card-body">
                @forelse ($candidate->references->filter->isComplete() as $ref)
                    <div class="doc-row">
                        <span class="idx mono">{{ $ref->slot }}</span>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold">{{ $ref->name }}</div>
                            <div class="text-muted-2" style="font-size:.83rem;">
                                {{ $ref->relationship }}@if ($ref->organisation) · {{ $ref->organisation }}@endif
                            </div>
                            <div class="mono text-faint" style="font-size:.76rem;">
                                @if ($ref->email)<a href="mailto:{{ $ref->email }}">{{ $ref->email }}</a>@endif
                                @if ($ref->email && $ref->fullPhone()) · @endif
                                @if ($ref->fullPhone()){{ $ref->fullPhone() }}@endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p class="mb-0">No references supplied. These are optional, so the
                            candidate can still submit without them.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card-flat">
            <div class="card-head">
                <div>
                    <span class="eyebrow">Full record</span>
                    <h2 style="font-size:1.05rem;">Activity</h2>
                </div>
            </div>
            <div class="card-body">
                @if ($timeline->isEmpty())
                    <p class="text-muted-2 mb-0" style="font-size:.88rem;">Nothing recorded yet.</p>
                @else
                    <div class="timeline">
                        @foreach ($timeline as $entry)
                            <div class="timeline-item {{ str_starts_with($entry->event, 'document.') ? 'accent' : '' }}">
                                <div class="what">{{ $entry->description }}</div>
                                <div class="when">
                                    {{ $entry->created_at?->format('d M Y, H:i') }} · {{ $entry->actor_label }}
                                    @if ($entry->ip_address) · <span class="mono">{{ $entry->ip_address }}</span> @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
