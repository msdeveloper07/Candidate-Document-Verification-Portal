<?php

namespace App\Repositories\Contracts;

interface AdminRepositoryInterface extends BaseRepositoryInterface
{
    public function activeRecruiters();
}
