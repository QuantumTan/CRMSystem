<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ActivityVisibilityScope
{
    public static function apply(Builder $query, User $user): Builder
    {
        if ($user->role !== 'sales') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->whereHas('lead', function (Builder $leadQuery) use ($user) {
                $leadQuery->where('assigned_user_id', $user->id);
            })->orWhereHas('customer', function (Builder $customerQuery) use ($user) {
                $customerQuery->where('assigned_user_id', $user->id);
            });
        });
    }
}