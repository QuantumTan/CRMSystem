<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'sales']);
    }

    public function view(User $user, Activity $activity): bool
    {
        // Admin & manager can view any activity
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }

        // Sales can view only if the activity belongs to a lead/customer assigned to them
        if ($user->role === 'sales') {
            if ($activity->lead_id && $activity->lead && $activity->lead->assigned_user_id === $user->id) {
                return true;
            }
            if ($activity->customer_id && $activity->customer && $activity->customer->assigned_user_id === $user->id) {
                return true;
            }

            return false;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'sales'], true);
    }

    public function update(User $user, Activity $activity): bool
    {
        // Admin can update any
        if ($user->role === 'admin') {
            return true;
        }

        // Managers are monitor/review only.
        if ($user->role === 'manager') {
            return false;
        }

        // If the user created the activity, allow
        if ($user->id === $activity->user_id) {
            return true;
        }

        // Sales can also update if the activity is linked to their assigned lead/customer
        if ($user->role === 'sales') {
            if ($activity->lead_id && $activity->lead && $activity->lead->assigned_user_id === $user->id) {
                return true;
            }
            if ($activity->customer_id && $activity->customer && $activity->customer->assigned_user_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }
}
