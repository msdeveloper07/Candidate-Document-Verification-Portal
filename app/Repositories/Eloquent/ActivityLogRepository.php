<?php

namespace App\Repositories\Eloquent;

use App\Models\ActivityLog;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;

class ActivityLogRepository extends BaseRepository implements ActivityLogRepositoryInterface
{
    public function __construct(ActivityLog $model)
    {
        parent::__construct($model);
    }

    public function forCandidate(int $candidateId, int $limit = 50)
    {
        return $this->query()
            ->where('candidate_id', $candidateId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function latest(int $limit = 12)
    {
        return $this->query()
            ->with(['candidate:id,reference_no,first_name,last_name'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
