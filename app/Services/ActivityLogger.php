<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Candidate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public function record(
        string $event,
        string $description,
        ?Candidate $candidate = null,
        array $properties = []
    ): ActivityLog {
        $admin = Auth::guard('admin')->user();

        return ActivityLog::create([
            'candidate_id' => $candidate?->id,
            'admin_id'     => $admin?->id,
            'actor_type'   => $this->actorType($admin),
            'actor_label'  => $this->actorLabel($admin, $candidate),
            'event'        => $event,
            'description'  => $description,
            'properties'   => $properties ?: null,
            'ip_address'   => Request::ip(),
            'user_agent'   => mb_substr((string) Request::userAgent(), 0, 255),
            'created_at'   => now(),
        ]);
    }

    private function actorType(?Admin $admin): string
    {
        if ($admin) {
            return 'admin';
        }

        return Auth::guard('candidate')->check() ? 'candidate' : 'system';
    }

    private function actorLabel(?Admin $admin, ?Candidate $candidate): string
    {
        if ($admin) {
            return $admin->name;
        }

        $authed = Auth::guard('candidate')->user();

        return $authed?->full_name ?? $candidate?->full_name ?? 'System';
    }
}
