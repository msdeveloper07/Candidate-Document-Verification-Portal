@extends('emails.layout')

@section('body')
    @if (! empty($reference))
        <p style="margin:0 0 6px;font-size:11px;letter-spacing:1.6px;text-transform:uppercase;color:#8FA3B7;">
            Reference {{ $reference }}
        </p>
    @endif

    @if (! empty($tpl['heading']))
        <h1 style="margin:0 0 14px;font-size:21px;font-weight:600;color:#0B1B2D;">{{ $tpl['heading'] }}</h1>
    @endif

    {{-- The admin writes plain paragraphs; blank lines separate them. --}}
    @foreach (preg_split('/\n\s*\n/', trim($tpl['body'])) as $paragraph)
        @continue (blank($paragraph))
        <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">{!! nl2br(e(trim($paragraph))) !!}</p>
    @endforeach

    {{-- Blocks the code owns, so the layout cannot be broken from the editor. --}}
    @if (! empty($otpCode))
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:4px 0 20px;">
            <tr>
                <td style="background:#F7F9FC;border:1px solid #DCE4ED;border-radius:12px;padding:16px 26px;
                           font-family:'Courier New',monospace;font-size:30px;letter-spacing:9px;color:#0B1B2D;font-weight:700;">
                    {{ $otpCode }}
                </td>
            </tr>
        </table>
    @endif

    @if (! empty($documents) && $documents->isNotEmpty())
        <p style="margin:0 0 8px;font-size:11px;letter-spacing:1.6px;text-transform:uppercase;color:#8FA3B7;">What we need</p>
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

    @if (! empty($decisionNote))
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
            <tr>
                <td style="background:{{ $decisionTone === 'danger' ? '#FBE9E7' : '#E8F3EC' }};
                           border-left:3px solid {{ $decisionTone === 'danger' ? '#A32F26' : '#1F6B45' }};
                           border-radius:8px;padding:14px 16px;font-size:14px;line-height:1.6;color:#12263A;">
                    <div style="font-size:11px;letter-spacing:1.4px;text-transform:uppercase;color:#5D7288;margin-bottom:4px;">
                        {{ $decisionTone === 'danger' ? 'What to fix' : 'Reviewer note' }}
                    </div>
                    {{ $decisionNote }}
                </td>
            </tr>
        </table>
    @endif

    @if (! empty($tpl['button_label']) && ! empty($link))
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 18px;">
            <tr>
                <td style="background:#16324F;border-radius:10px;">
                    <a href="{{ $link }}" style="display:inline-block;padding:13px 26px;color:#FFFFFF;
                              font-size:15px;font-weight:600;text-decoration:none;">{{ $tpl['button_label'] }}</a>
                </td>
            </tr>
        </table>
    @endif

    @if (! empty($tpl['footer_note']))
        <p style="margin:0 0 6px;font-size:13px;color:#5D7288;">{{ $tpl['footer_note'] }}</p>
    @endif

    @if (! empty($link))
        <p style="margin:16px 0 0;font-size:12px;color:#8FA3B7;word-break:break-all;">
            If the button does not work, paste this into your browser:<br>{{ $link }}
        </p>
    @endif
@endsection
