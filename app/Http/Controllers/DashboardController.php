<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match ($user?->role) {
            'admin' => redirect()->route('dashboard.admin'),
            'manager' => redirect()->route('dashboard.manager'),
            'sales' => redirect()->route('dashboard.sales'),
            default => abort(403, 'Unauthorized.'),
        };
    }

    public function admin(): View
    {
        $this->ensureRole('admin');

        return view('dashboard.admin', $this->buildDashboardData('admin'));
    }

    public function manager(Request $request): View
    {
        $this->ensureRole('manager');

        return view('dashboard.manager', $this->buildDashboardData('manager'));
    }

    public function sales(Request $request): View
    {
        $this->ensureRole('sales');

        return view('dashboard.sales', $this->buildDashboardData('sales', (int) $request->user()->id));
    }

    private function ensureRole(string $role): void
    {
        abort_unless(request()->user()?->hasRole($role), 403, 'Unauthorized. Insufficient permissions.');
    }

    private function buildDashboardData(string $role, ?int $userId = null): array
    {
        $customers = Customer::query();
        $leads = Lead::query();
        $followUps = FollowUp::query();
        $activities = Activity::with(['customer', 'lead', 'user'])->latest('activity_date');

        if ($role === 'sales' && $userId !== null) {
            $customers->where('assigned_user_id', $userId);
            $leads->where('assigned_user_id', $userId);
            $followUps->where('user_id', $userId);
            $activities->where('user_id', $userId);
        }

        $leadStatuses = collect(Lead::getStatuses())
            ->mapWithKeys(fn (string $status): array => [$status => 0]);

        $leadStatusCounts = (clone $leads)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        /** @var Collection<int, int> $mergedLeadStatusCounts */
        $mergedLeadStatusCounts = $leadStatuses->merge($leadStatusCounts)->map(fn ($count): int => (int) $count);

        $pendingFollowUps = (clone $followUps)->where('status', 'pending');

        return [
            'data' => [
                'scope' => $role,
                'totalCustomers' => $customers->count(),
                'totalLeads' => (clone $leads)->count(),
                'totalActiveLeads' => (clone $leads)->whereNotIn('status', ['won', 'lost'])->count(),
                'completedFollowUps' => (clone $followUps)->where('status', 'completed')->count(),
                'pendingFollowUps' => (clone $pendingFollowUps)->count(),
                'overdueFollowUps' => (clone $pendingFollowUps)
                    ->whereDate('due_date', '<', now()->toDateString())
                    ->count(),
                'leadStatusCounts' => $mergedLeadStatusCounts,
                'recentActivities' => $activities->limit(5)->get(),
                'upcomingFollowUps' => (clone $pendingFollowUps)
                    ->with(['customer', 'lead', 'user'])
                    ->where('status', 'pending')
                    ->whereDate('due_date', '>=', now()->toDateString())
                    ->orderBy('due_date')
                    ->limit(5)
                    ->get(),
            ],
        ];
    }
}
