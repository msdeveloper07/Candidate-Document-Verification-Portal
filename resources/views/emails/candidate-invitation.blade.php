@extends('emails.layout')

@section('body')
    <p style="margin:0 0 6px;font-size:11px;letter-spacing:1.6px;text-transform:uppercase;color:#8FA3B7;">
        Reference {{ $candidate->reference_no }}
    </p>

    <h1 style="margin:0 0 14px;font-size:21px;font-weight:600;color:#0B1B2D;">
        {{ $candidate->first_name }}, please send us your documents
    </h1>

    <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
        Rather than emailing attachments back and forth, upload everything through our secure page.
        You will confirm your mobile number with a one-time code, then upload each item.
    </p>

    @if ($documents->isNotEmpty())
        <p style="margin:0 0 8px;font-size:11px;letter-spacing:1.6px;text-transform:uppercase;color:#8FA3B7;">
            What we need
        </p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px;">
            @foreach ($documents as $doc)
                <tr>
                    <td style="padding:8px 0;border-bottom:1px solid #EDF1F6;font-size:14px;">
                        <span style="color:#B4832B;font-weight:600;">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        &nbsp;{{ $doc->name }}
                        @if ($doc->description)
                            <div style="color:#5D7288;font-size:12.5px;margin-top:2px;">{{ $doc->description }}</div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 18px;">
        <tr>
            <td style="background:#16324F;border-radius:10px;">
                <a href="{{ $link }}" style="display:inline-block;padding:13px 26px;color:#FFFFFF;
                          font-size:15px;font-weight:600;text-decoration:none;">
                    Upload my documents
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 6px;font-size:13px;color:#5D7288;">
        This link works until <strong style="color:#12263A;">{{ $expires->format('d F Y') }}</strong>
        and is meant only for you. Do not forward it.
    </p>

    <p style="margin:16px 0 0;font-size:12px;color:#8FA3B7;word-break:break-all;">
        If the button does not work, paste this into your browser:<br>{{ $link }}
    </p>
@endsection
