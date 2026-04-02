<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class LeadFilter
{
    public function apply(Builder $query, array $filters, bool $isSales = false): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->when(
                ($filters['assigned_user'] ?? null) && ! $isSales,
                fn ($q) => $q->where('assigned_user_id', $filters['assigned_user'])
            )
            ->when($filters['search'] ?? null, fn ($q, $v) => $this->applySearch($q, $v));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        $search = addcslashes($search, '\\%_');

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}
