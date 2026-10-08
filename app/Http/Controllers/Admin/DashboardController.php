<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\CandidateDocumentRepositoryInterface;
use App\Repositories\Contracts\CandidateRepositoryInterface;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly CandidateRepositoryInterface $candidates,
        private readonly CandidateDocumentRepositoryInterface $documents,
        private readonly ActivityLogRepositoryInterface $activity,
    ) {
    }

    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts'        => $this->candidates->dashboardCounts(),
            'documentStats' => $this->documents->statusCounts(),
            'recent'        => $this->candidates->recent(6),
            'awaiting'      => $this->documents->pendingReview(5),
            'activity'      => $this->activity->latest(10),
            'intake'        => $this->candidates->weeklyIntake(8),
        ]);
    }
}
