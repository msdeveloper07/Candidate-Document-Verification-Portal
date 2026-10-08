<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    /** key => group, so each value lands in the right bucket. */
    private const FIELDS = [
        'agency_name'       => 'branding',
        'agency_tagline'    => 'branding',
        'support_email'     => 'branding',
        'support_phone'     => 'branding',
        'invite_valid_days' => 'workflow',
        'otp_ttl_minutes'   => 'workflow',
    ];

    public function __construct(private ActivityLogger $logger)
    {
    }

    public function edit(): View
    {
        return view('admin.settings', [
            'settings' => Setting::cached(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $before = Setting::cached();

        foreach (self::FIELDS as $key => $group) {
            Setting::put($key, (string) $request->input($key, ''), $group);
        }

        if (($before['agency_name'] ?? null) !== $request->input('agency_name')) {
            $this->logger->record(
                'settings.renamed',
                'Agency name changed to "'.$request->input('agency_name').'"'
            );
        } else {
            $this->logger->record('settings.updated', 'Updated the agency settings');
        }

        return back()->with('status', 'Saved. The new details appear everywhere immediately.');
    }
}
