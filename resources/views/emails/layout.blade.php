<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('portal.agency_name') }}</title>
</head>
<body style="margin:0;padding:0;background:#EDF1F6;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#12263A;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EDF1F6;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:560px;background:#FFFFFF;border:1px solid #DCE4ED;border-radius:14px;overflow:hidden;">

                <tr>
                    <td style="background:#0B1B2D;padding:20px 26px;">
                        <table role="presentation" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="width:32px;height:32px;background:#D9A441;border-radius:8px;text-align:center;
                                           font-weight:700;font-size:14px;color:#0B1B2D;">
                                    {{ mb_substr(config('portal.agency_name'), 0, 1) }}
                                </td>
                                <td style="padding-left:10px;">
                                    <div style="color:#FFFFFF;font-size:15px;font-weight:600;">{{ config('portal.agency_name') }}</div>
                                    <div style="color:#D9A441;font-size:10px;letter-spacing:1.6px;text-transform:uppercase;">Document intake</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 26px;">
                        @yield('body')
                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 26px;background:#F7F9FC;border-top:1px solid #DCE4ED;
                               font-size:12px;line-height:1.6;color:#5D7288;">
                        You received this because {{ config('portal.agency_name') }} is processing your application.
                        @if ($support = \App\Models\Setting::config('support_email'))
                            Questions? Write to <a href="mailto:{{ $support }}" style="color:#16324F;">{{ $support }}</a>.
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
