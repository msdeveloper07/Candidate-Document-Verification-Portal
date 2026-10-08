{{--
    The signature element. One question, one answer: am I cleared?
    The brass fill is the progress meter — the card IS the gauge.
--}}
@php
    $done    = $summary['approved'];
    $items   = $summary['line_items'];
    $cleared = $candidate->hasSubmittedEverything() && $summary['approved'] === $summary['total'];
@endphp

<section class="clearance {{ $cleared ? 'is-cleared' : '' }}" data-progress-panel>
    <div class="clearance-fill" style="height: {{ max($summary['percent'], 4) }}%;" data-clearance-fill></div>

    <div class="clearance-id">
        <span class="clearance-name">{{ $candidate->full_name }}</span>
        <span>{{ $candidate->reference_no }}</span>
    </div>

    <div class="clearance-count">
        <span class="n" data-count-approved>{{ $done }}</span>
        <span class="of">of {{ $items }}</span>
        <span class="d-none" data-count-total>{{ $summary['total'] }}</span>
    </div>

    <div class="clearance-state" data-progress-headline>
        @if ($cleared)
            Cleared for assignment
        @elseif ($summary['rejected'] > 0)
            {{ $summary['rejected'] }} {{ \Illuminate\Support\Str::plural('item', $summary['rejected']) }} need a new copy
        @elseif ($summary['missing'] > 0)
            Still to send
        @else
            With our team now
        @endif
    </div>

    <div class="clearance-sub">
        @if ($cleared)
            Everything has been checked and accepted. Nothing more is needed from you.
        @elseif ($summary['rejected'] > 0)
            Open the items marked below — each one says what to change.
        @elseif ($summary['missing'] > 0)
            You can stop and come back; anything you have sent is saved.
        @else
            We are checking your files. You will get an email as each one is decided.
        @endif
    </div>

    <div class="meter" data-meter>
        @foreach ($checklist as $item)
            @php($doc = $item['document'])
            <span class="notch {{ $doc?->status->value === 'approved' ? 'done' : ($doc?->status->value === 'rejected' ? 'redo' : ($doc ? 'review' : '')) }}"
                  data-notch-for="{{ $item['type']->id }}"></span>
        @endforeach
    </div>
</section>
