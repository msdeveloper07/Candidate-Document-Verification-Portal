<?php

namespace App\Repositories\Contracts;

use App\Models\Candidate;

interface CandidateRepositoryInterface extends BaseRepositoryInterface
{
    public function findByInviteToken(string $token): ?Candidate;

    public function findByPhone(string $dialCode, string $phone): ?Candidate;

    public function dashboardCounts(): array;

    public function recent(int $limit = 8);

    public function weeklyIntake(int $weeks = 8): array;
}
