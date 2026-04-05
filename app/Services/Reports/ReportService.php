<?php

namespace App\Services\Reports;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    public function build(?string $fromDate = null, ?string $toDate = null, ?User $user = null): array
    {
        $context = $this->buildContext($user);

        $data = [
            'totalCustomers' => $this->getTotalCustomers($context, $fromDate, $toDate),
            'leadsByStatus' => $this->getLeadsByStatus($context, $fromDate, $toDate),
            'salesPipelineSummary' => $this->getSalesPipelineSummary($context, $fromDate, $toDate),
            'userActivity' => $this->getUserActivity($context, $fromDate, $toDate),
            'followUpCompletion' => $this->getFollowUpCompletion($context, $fromDate, $toDate),
            'activitiesByType' => $this->getActivitiesByType($context, $fromDate, $toDate),
            'filters' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
        ];

        $totalFollowUps = $data['followUpCompletion']['completed'] + $data['followUpCompletion']['pending'];

        $data['followUpCompletion']['completion_rate'] = $totalFollowUps > 0
            ? round(($data['followUpCompletion']['completed'] / $totalFollowUps) * 100, 2)
            : 0.0;

        return $data;
    }

    private function buildContext(?User $user): array
    {
        $isManager = $user?->hasRole('manager') ?? false;

        return [
            'isManager' => $isManager,
            'salesUserIds' => $isManager
                ? User::query()
                    ->where('role', 'sales')
                    ->pluck('id')
                    ->all()
                : [],
        ];
    }

    private function getTotalCustomers(array $context, ?string $fromDate, ?string $toDate): int
    {
        $query = Customer::query();

        $this->restrictAssignedQuery($query, $context, 'assigned_user_id');
        $this->applyDateRange($query, 'created_at', $fromDate, $toDate);

        return $query->count();
    }

    private function getLeadsByStatus(array $context, ?string $fromDate, ?string $toDate): Collection
    {
        $statuses = Lead::getStatuses();

        $query = Lead::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status');

        $this->restrictAssignedQuery($query, $context, 'assigned_user_id');
        $this->applyDateRange($query, 'created_at', $fromDate, $toDate);

        $counts = $query->pluck('total', 'status');

        return collect($statuses)->map(function (string $status) use ($counts): array {
            return [
                'status' => $status,
                'total' => (int) ($counts[$status] ?? 0),
            ];
        });
    }

    private function getSalesPipelineSummary(array $context, ?string $fromDate, ?string $toDate): array
    {
        $baseQuery = Lead::query();

        $this->restrictAssignedQuery($baseQuery, $context, 'assigned_user_id');
        $this->applyDateRange($baseQuery, 'created_at', $fromDate, $toDate);

        $totalLeads = (clone $baseQuery)->count();
        $wonLeads = (clone $baseQuery)->where('status', 'won')->count();
        $lostLeads = (clone $baseQuery)->where('status', 'lost')->count();

        $totalExpectedValueQuery = Lead::query();
        $this->restrictAssignedQuery($totalExpectedValueQuery, $context, 'assigned_user_id');
        $this->applyDateRange($totalExpectedValueQuery, 'created_at', $fromDate, $toDate);

        $activeExpectedValueQuery = Lead::query()
            ->whereNotIn('status', ['won', 'lost']);

        $this->restrictAssignedQuery($activeExpectedValueQuery, $context, 'assigned_user_id');
        $this->applyDateRange($activeExpectedValueQuery, 'created_at', $fromDate, $toDate);

        return [
            'total_leads' => $totalLeads,
            'active_pipeline_leads' => max($totalLeads - $wonLeads - $lostLeads, 0),
            'won_leads' => $wonLeads,
            'lost_leads' => $lostLeads,
            'total_expected_value' => (float) $totalExpectedValueQuery->sum('expected_value'),
            'active_expected_value' => (float) $activeExpectedValueQuery->sum('expected_value'),
        ];
    }

    private function getUserActivity(array $context, ?string $fromDate, ?string $toDate)
    {
        $activityTotalsByUser = Activity::query()
            ->selectRaw('user_id, COUNT(*) as total_activities')
            ->groupBy('user_id');

        if ($context['isManager']) {
            $activityTotalsByUser->whereIn('user_id', $context['salesUserIds']);
        }

        $this->applyDateRange($activityTotalsByUser, 'activity_date', $fromDate, $toDate);

        return User::query()
            ->leftJoinSub($activityTotalsByUser, 'activity_totals', function ($join): void {
                $join->on('users.id', '=', 'activity_totals.user_id');
            })
            ->selectRaw('users.name, users.role, COALESCE(activity_totals.total_activities, 0) as total_activities')
            ->when(
                $context['isManager'],
                fn (Builder $query) => $query->where('users.role', 'sales')
            )
            ->orderByDesc('total_activities')
            ->get();
    }

    private function getFollowUpCompletion(array $context, ?string $fromDate, ?string $toDate): array
    {
        $completedQuery = FollowUp::query()->where('status', 'completed');
        $pendingQuery = FollowUp::query()->where('status', 'pending');
        $overdueQuery = FollowUp::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', now()->toDateString());

        $this->restrictUserQuery($completedQuery, $context, 'user_id');
        $this->restrictUserQuery($pendingQuery, $context, 'user_id');
        $this->restrictUserQuery($overdueQuery, $context, 'user_id');

        $this->applyDateRange($completedQuery, 'due_date', $fromDate, $toDate);
        $this->applyDateRange($pendingQuery, 'due_date', $fromDate, $toDate);
        $this->applyDateRange($overdueQuery, 'due_date', $fromDate, $toDate);

        return [
            'completed' => $completedQuery->count(),
            'pending' => $pendingQuery->count(),
            'overdue' => $overdueQuery->count(),
        ];
    }

    private function getActivitiesByType(array $context, ?string $fromDate, ?string $toDate)
    {
        $query = Activity::query()
            ->selectRaw('activity_type, COUNT(*) as total')
            ->groupBy('activity_type')
            ->orderByDesc('total');

        $this->restrictUserQuery($query, $context, 'user_id');
        $this->applyDateRange($query, 'activity_date', $fromDate, $toDate);

        return $query->get();
    }

    private function restrictAssignedQuery(Builder $query, array $context, string $column): void
    {
        if (! $context['isManager']) {
            return;
        }

        $query->where(function (Builder $builder) use ($context, $column): void {
            $builder->whereIn($column, $context['salesUserIds'])
                ->orWhereNull($column);
        });
    }

    private function restrictUserQuery(Builder $query, array $context, string $column): void
    {
        if (! $context['isManager']) {
            return;
        }

        $query->whereIn($column, $context['salesUserIds']);
    }

    private function applyDateRange(Builder $query, string $dateColumn, ?string $fromDate, ?string $toDate): void
    {
        if ($fromDate !== null) {
            $query->whereDate($dateColumn, '>=', $fromDate);
        }

        if ($toDate !== null) {
            $query->whereDate($dateColumn, '<=', $toDate);
        }
    }
}