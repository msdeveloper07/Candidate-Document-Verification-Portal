@extends('emails.layout')

@section('body')
    <h1 style="margin:0 0 12px;font-size:20px;font-weight:600;color:#0B1B2D;">Your verification code</h1>

    <p style="margin:0 0 18px;font-size:15px;line-height:1.6;">
        Enter this code on the upload page to continue.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 18px;">
        <tr>
            <td style="background:#F7F9FC;border:1px solid #DCE4ED;border-radius:12px;padding:16px 28px;
                       font-family:'Courier New',monospace;font-size:30px;font-weight:600;
                       letter-spacing:8px;color:#0B1B2D;">
                {{ $code }}
            </td>
        </tr>
    </table>

    <p style="margin:0 0 6px;font-size:13px;color:#5D7288;">
        It expires in {{ $minutes }} minutes.
    </p>

    <p style="margin:14px 0 0;font-size:13px;color:#B0231A;">
        Nobody from {{ config('portal.agency_name') }} will ever ask you for this code. If you did not
        request it, ignore this email.
    </p>
@endsection
