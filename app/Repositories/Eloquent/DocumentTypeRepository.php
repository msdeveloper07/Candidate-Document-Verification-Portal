<?php

namespace App\Repositories\Eloquent;

use App\Models\DocumentType;
use App\Repositories\Contracts\DocumentTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DocumentTypeRepository extends BaseRepository implements DocumentTypeRepositoryInterface
{
    public function __construct(DocumentType $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn ($q, $t) => $q->where('name', 'like', "%{$t}%"))
            ->orderBy('sort_order');
    }

    public function active(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }

    public function defaults(): Collection
    {
        return $this->query()->active()->where('is_required_by_default', true)->orderBy('sort_order')->get();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $position => $id) {
            $this->query()->whereKey($id)->update(['sort_order' => ($position + 1) * 10]);
        }
    }
}
