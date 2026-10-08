<?php

namespace App\Repositories\Eloquent;

use App\Enums\DocumentStatus;
use App\Models\Candidate;
use App\Models\CandidateDocument;
use App\Repositories\Contracts\CandidateDocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CandidateDocumentRepository extends BaseRepository implements CandidateDocumentRepositoryInterface
{
    public function __construct(CandidateDocument $model)
    {
        parent::__construct($model);
    }

    public function forCandidate(Candidate $candidate): Collection
    {
        return $this->query()
            ->where('candidate_id', $candidate->id)
            ->with(['documentType', 'reviewedBy:id,name'])
            ->get();
    }

    public function existing(int $candidateId, int $documentTypeId): ?CandidateDocument
    {
        return $this->query()
            ->where('candidate_id', $candidateId)
            ->where('document_type_id', $documentTypeId)
            ->first();
    }

    public function pendingReview(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        return $this->query()
            ->with(['candidate:id,reference_no,first_name,last_name,country_name', 'documentType:id,name,icon'])
            ->when(
                $filters['status'] ?? null,
                // 'awaiting' is a bucket, not a stored value: uploaded + under review.
                fn ($q, $s) => $s === 'awaiting'
                    ? $q->pending()
                    : $q->where('status', $s),
                fn ($q) => $q->pending()
            )
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('document_type_id', $t))
            ->when($filters['search'] ?? null, function ($q, $term) {
                $q->whereHas('candidate', fn ($c) => $c->search($term));
            })
            ->orderBy('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function statusCounts(): array
    {
        $rows = $this->query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'uploaded' => (int) $rows->get(DocumentStatus::Uploaded->value, 0),
            'review'   => (int) $rows->get(DocumentStatus::UnderReview->value, 0),
            // Anything the recruiter still has to act on.
            'pending'  => (int) $rows->get(DocumentStatus::Uploaded->value, 0)
                        + (int) $rows->get(DocumentStatus::UnderReview->value, 0),
            'approved' => (int) $rows->get(DocumentStatus::Approved->value, 0),
            'rejected' => (int) $rows->get(DocumentStatus::Rejected->value, 0),
        ];
    }
}
