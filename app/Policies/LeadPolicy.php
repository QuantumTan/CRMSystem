<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole('admin', 'manager', 'sales');
    }

    public function view(User $user, Lead $lead): bool
    {
        if ($user->hasAnyRole('admin', 'manager')) {
            return true;
        }

        return $user->hasRole('sales') && (int) $lead->assigned_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole('admin', 'sales');
    }

    public function update(User $user, Lead $lead): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('sales') && (int) $lead->assigned_user_id === $user->id;
    }

    public function updateStatus(User $user, Lead $lead): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('sales') && (int) $lead->assigned_user_id === $user->id;
    }

    public function assign(User $user,  Lead $lead): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user,  Lead $lead): bool
    {
        return $user->hasRole('admin');
    }
}
