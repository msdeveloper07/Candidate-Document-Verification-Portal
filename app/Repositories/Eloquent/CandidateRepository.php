<?php

namespace App\Repositories\Eloquent;

use App\Enums\CandidateStatus;
use App\Models\Candidate;
use App\Repositories\Contracts\CandidateRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class CandidateRepository extends BaseRepository implements CandidateRepositoryInterface
{
    public function __construct(Candidate $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->with(['invitedBy:id,name', 'requirements', 'documents'])
            ->search($filters['search'] ?? null)
            ->status($filters['status'] ?? null)
            ->when($filters['recruiter'] ?? null, fn ($q, $id) => $q->where('invited_by', $id))
            ->when($filters['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderByDesc('created_at');
    }

    public function findByInviteToken(string $token): ?Candidate
    {
        return $this->query()->where('invite_token', $token)->first();
    }

    public function findByPhone(string $dialCode, string $phone): ?Candidate
    {
        return $this->query()
            ->where('dial_code', $dialCode)
            ->where('phone', $phone)
            ->first();
    }

    public function dashboardCounts(): array
    {
        $rows = $this->query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total'       => (int) $rows->sum(),
            'invited'     => (int) $rows->get(CandidateStatus::Invited->value, 0),
            'in_progress' => (int) $rows->get(CandidateStatus::InProgress->value, 0),
            'submitted'   => (int) $rows->get(CandidateStatus::Submitted->value, 0)
                             + (int) $rows->get(CandidateStatus::UnderReview->value, 0),
            'approved'    => (int) $rows->get(CandidateStatus::Approved->value, 0),
            'rejected'    => (int) $rows->get(CandidateStatus::Rejected->value, 0),
        ];
    }

    public function recent(int $limit = 8)
    {
        return $this->query()
            ->with(['requirements', 'documents', 'invitedBy:id,name'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /** Intake per ISO week, for the dashboard sparkline. */
    public function weeklyIntake(int $weeks = 8): array
    {
        $start = now()->subWeeks($weeks - 1)->startOfWeek();

        $rows = $this->query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn ($c) => $c->created_at->startOfWeek()->format('Y-m-d'))
            ->map->count();

        $series = [];
        for ($i = 0; $i < $weeks; $i++) {
            $week = $start->copy()->addWeeks($i);
            $series[] = [
                'label' => $week->format('d M'),
                'value' => (int) ($rows[$week->format('Y-m-d')] ?? 0),
            ];
        }

        return $series;
    }
}
