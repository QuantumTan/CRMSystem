<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ActivityFilter
{
    public function __construct(
        protected Request $request
    ) {}

    public function apply(Builder $query): Builder
    {
        return $query
            ->when(
                $this->request->filled('type'),
                fn(Builder $q) => $q->where(
                    'activity_type',
                    $this->request->string('type')->toString()
                )
            )
            ->when(
                $this->request->filled('search'),
                fn(Builder $q) => $q->where(
                    'description',
                    'like',
                    '%' . $this->request->string('search')->toString() . '%'
                )
            )
            ->when(
                $this->request->filled('user_id'),
                fn(Builder $q) => $q->where(
                    'user_id',
                    $this->request->integer('user_id')
                )
            );
    }
}
