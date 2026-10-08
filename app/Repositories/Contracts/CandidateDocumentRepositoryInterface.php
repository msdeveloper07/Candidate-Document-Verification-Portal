<?php

namespace App\Repositories\Contracts;

use App\Models\Candidate;
use App\Models\CandidateDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CandidateDocumentRepositoryInterface extends BaseRepositoryInterface
{
    public function forCandidate(Candidate $candidate): Collection;

    public function existing(int $candidateId, int $documentTypeId): ?CandidateDocument;

    public function pendingReview(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function statusCounts(): array;
}
