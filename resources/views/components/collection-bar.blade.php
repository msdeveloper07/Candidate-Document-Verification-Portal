@props(['summary'])

@php($total = max($summary['total'], 1))

<div class="collection-bar" role="img"
     aria-label="{{ $summary['approved'] }} approved, {{ $summary['pending'] }} in review, {{ $summary['rejected'] }} to redo, {{ $summary['missing'] }} not sent">
    @if ($summary['approved']) <span class="seg-approved" style="flex-grow:{{ $summary['approved'] }}"></span> @endif
    @if ($summary['pending'])  <span class="seg-pending"  style="flex-grow:{{ $summary['pending'] }}"></span>  @endif
    @if ($summary['rejected']) <span class="seg-rejected" style="flex-grow:{{ $summary['rejected'] }}"></span> @endif
    @if ($summary['missing'])  <span class="seg-missing"  style="flex-grow:{{ $summary['missing'] }}"></span>  @endif
</div>
