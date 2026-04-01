<?php

namespace App\Policies;

use App\Models\FollowUp;
use App\Models\User;

class FollowUpPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'sales'], true);
    }

    public function view(User $user, FollowUp $followUp): bool
    {
        if (in_array($user->role, ['admin', 'manager'], true)) {
            return true;
        }

        if ($user->role !== 'sales') {
            return false;
        }

        return $followUp->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'sales'], true);
    }

    public function update(User $user, FollowUp $followUp): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'manager') {
            return false;
        }

        if ($user->role === 'sales') {
            if ($followUp->status === 'completed') {
                return false;
            }

            return $followUp->user_id === $user->id;
        }

        return false;
    }
}
