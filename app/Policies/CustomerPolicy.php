<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function view(User $user, Customer $customer): bool
    {
        if ($user->hasRole('sales')) {
            return (int) $customer->assigned_user_id === $user->id
                && $customer->assignment_status === 'approved';
        }

        return true;
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->view($user, $customer);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasRole('admin');
    }
}
