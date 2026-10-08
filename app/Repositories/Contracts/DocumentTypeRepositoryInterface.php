<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface DocumentTypeRepositoryInterface extends BaseRepositoryInterface
{
    public function active(): Collection;

    public function defaults(): Collection;

    public function reorder(array $orderedIds): void;
}
