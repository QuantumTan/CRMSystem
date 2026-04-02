<?php

namespace App\Services;

use App\Filters\LeadFilter;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LeadService
{
    public const STATUS_OPTIONS = ['new', 'contacted', 'qualified', 'proposal_sent', 'negotiation', 'won', 'lost'];

    public const PRIORITY_OPTIONS = ['low', 'medium', 'high', 'critical'];

    public const LOST_CATEGORIES = [
        'budget' => 'Budget too high',
        'competitor' => 'Chose competitor',
        'timing' => 'Wrong timing',
        'not_interested' => 'Not interested',
        'no_decision' => 'No decision maker',
        'other' => 'Other',
    ];

    public function __construct(private LeadFilter $filter) {}

    public function getFilteredQuery(array $filters, bool $isSales = false): Builder
    {
        return $this->filter->apply(
            Lead::query()->with(['assignedUser', 'convertedToCustomer'])->latest(),
            $filters,
            $isSales
        );
    }

    public function groupByStatus(Collection $leads): array
    {
        $grouped = [];
        foreach (self::STATUS_OPTIONS as $status) {
            $grouped[$status] = $leads->where('status', $status)->values();
        }

        return $grouped;
    }

    public function assignableUsers(?User $user): Collection
    {
        if ($user?->hasRole('sales')) {
            return User::query()->whereKey($user->id)->orderBy('name')->get();
        }

        return User::query()->whereIn('role', ['admin', 'manager', 'sales'])->orderBy('name')->get();
    }
}
