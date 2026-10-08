<?php

namespace App\Repositories\Contracts;

interface ActivityLogRepositoryInterface extends BaseRepositoryInterface
{
    public function forCandidate(int $candidateId, int $limit = 50);

    public function latest(int $limit = 12);
}
