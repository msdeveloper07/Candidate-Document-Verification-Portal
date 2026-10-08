@extends('emails.layout')

@php($approved = $document->status->value === 'approved')

@section('body')
    <p style="margin:0 0 6px;font-size:11px;letter-spacing:1.6px;text-transform:uppercase;color:#8FA3B7;">
        Reference {{ $candidate->reference_no }}
    </p>

    <h1 style="margin:0 0 14px;font-size:20px;font-weight:600;color:#0B1B2D;">
        {{ $approved ? $document->documentType->name.' is approved' : 'Please re-upload your '.$document->documentType->name }}
    </h1>

    @if ($approved)
        <p style="margin:0 0 18px;font-size:15px;line-height:1.6;">
            Thank you, {{ $candidate->first_name }}. We have checked this document and nothing further is needed for it.
        </p>
    @else
        <p style="margin:0 0 14px;font-size:15px;line-height:1.6;">
            {{ $candidate->first_name }}, we could not accept this document as it is. Here is what to change:
        </p>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
            <tr>
                <td style="background:#FBE8E6;border-left:3px solid #B0231A;border-radius:8px;
                           padding:13px 16px;font-size:14px;line-height:1.6;color:#8A1B14;">
                    {{ $document->remarks }}
                </td>
            </tr>
        </table>

        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 16px;">
            <tr>
                <td style="background:#16324F;border-radius:10px;">
                    <a href="{{ $link }}" style="display:inline-block;padding:12px 24px;color:#FFFFFF;
                              font-size:15px;font-weight:600;text-decoration:none;">
                        Upload a new copy
                    </a>
                </td>
            </tr>
        </table>
    @endif

    <p style="margin:0;font-size:13px;color:#5D7288;">
        Reviewed on {{ $document->reviewed_at?->format('d F Y') }}.
    </p>
@endsection
