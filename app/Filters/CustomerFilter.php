<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class CustomerFilter
{
    public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['assignment_status'] ?? null, fn ($q, $v) => $q->where('assignment_status', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['assigned_user_id'] ?? null, fn ($q, $v) => $q->where('assigned_user_id', (int) $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $this->applySearch($q, $v));
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        $search = addcslashes($search, '\\%_');

        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('company', 'like', "%{$search}%");
        });
    }
}
