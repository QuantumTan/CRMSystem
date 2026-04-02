<?php

namespace App\Services;

use App\Filters\CustomerFilter;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CustomerService
{
    public function __construct(private CustomerFilter $filter) {}

    public function getFilteredQuery(array $filters): Builder
    {
        return $this->filter->apply(
            Customer::query()->with(['assignedUser', 'assignmentReviewer'])->latest(),
            $filters
        );
    }

    public function getStats(): array
    {
        $base = Customer::query();
        $lastMonth = now()->subMonth();

        return [
            'customerThisMonth' => (clone $base)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'customerLastMonth' => (clone $base)->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->count(),
            'customerSpecificMonth' => (clone $base)->whereMonth('created_at', 1)->whereYear('created_at', 2025)->count(),
            'customerThisYear' => (clone $base)->whereYear('created_at', now()->year)->count(),
            'customerIsActive' => (clone $base)->where('status', 'active')->count(),
            'customerIsInactive' => (clone $base)->where('status', 'inactive')->count(),
            'totalCustomers' => (clone $base)->count(),
        ];
    }

    public function assignableUsers()
    {
        return User::query()->where('role', 'sales')->orderBy('name')->get();
    }

    public function prepareAssignmentPayload(array $payload, ?User $user): array
    {
        if ($user?->hasRole('sales')) {
            $payload['assigned_user_id'] = $user->id;
        }

        if (! empty($payload['assigned_user_id'])) {
            $payload['assignment_status'] = 'pending';
            $payload['assignment_reviewed_by'] = null;
            $payload['assignment_reviewed_at'] = null;
        }

        return $payload;
    }

    public function hasAssignmentChanged(array $payload, Customer $customer): bool
    {
        return array_key_exists('assigned_user_id', $payload)
            && (int) ($payload['assigned_user_id'] ?? 0) !== (int) ($customer->assigned_user_id ?? 0);
    }
}
