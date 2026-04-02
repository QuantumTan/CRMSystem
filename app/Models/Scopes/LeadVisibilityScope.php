<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LeadVisibilityScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        /** @var User|null $user */
        $user = auth()->guard()->user();

        if ($user?->hasRole('sales')) {
            $builder->where('assigned_user_id', $user->id);
        }
    }
}
