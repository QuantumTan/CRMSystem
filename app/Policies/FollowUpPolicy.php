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

        if ($followUp->user_id === $user->id) {
            return true;
        }

        if ($followUp->lead && $followUp->lead->assigned_user_id === $user->id) {
            return true;
        }

        if ($followUp->customer && $followUp->customer->assigned_user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'sales'], true);
    }

    public function update(User $user, FollowUp $followUp): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'manager') {
            return $followUp->status !== 'completed';
        }

        if ($user->role === 'sales') {
            if ($followUp->status === 'completed') {
                return false;
            }

            if ($followUp->user_id === $user->id) {
                return true;
            }

            if ($followUp->lead && $followUp->lead->assigned_user_id === $user->id) {
                return true;
            }

            if ($followUp->customer && $followUp->customer->assigned_user_id === $user->id) {
                return true;
            }
        }

        return false;
    }
}
