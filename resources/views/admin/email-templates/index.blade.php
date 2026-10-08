@extends('layouts.admin')
@section('title', 'Email templates')

@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">Configuration</span>
        <h1>Email templates</h1>
        <p class="lede">The wording of every message the portal sends. The layout and branding stay fixed —
            you edit the words.</p>
    </div>
</div>

@include('layouts.partials.flash')

<div class="d-flex flex-column gap-2">
    @foreach ($templates as $template)
        <div class="card-flat">
            <div class="card-body d-flex align-items-start gap-3 flex-wrap">
                <div class="flex-grow-1 min-width-0">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h2 style="font-size:1rem;margin:0;">{{ $template->name }}</h2>
                        @unless ($template->is_active)
                            <x-stamp tone="neutral" label="Using default" />
                        @endunless
                        @if ($template->differsFromDefault())
                            <x-stamp tone="info" label="Edited" />
                        @endif
                    </div>

                    <p class="text-muted-2 mb-1" style="font-size:.86rem;">{{ $template->description }}</p>

                    <div class="mono text-faint" style="font-size:.76rem;">
                        Subject: {{ $template->subject }}
                    </div>

                    @if ($template->updatedBy)
                        <div class="text-faint mt-1" style="font-size:.74rem;">
                            Last changed by {{ $template->updatedBy->name }}, {{ $template->updated_at->diffForHumans() }}
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="{{ route('admin.email-templates.preview', $template) }}" target="_blank" rel="noopener"
                       class="btn btn-quiet btn-sm">Preview</a>
                    <a href="{{ route('admin.email-templates.edit', $template) }}" class="btn btn-ink btn-sm">Edit</a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
