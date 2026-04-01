<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return view('dashboard.admin', $this->buildDashboardData());
    }

    public function manager(Request $request): View
    {
        return view('dashboard.manager', $this->buildDashboardData());
    }

    public function sales(Request $request): View
    {
        return view('dashboard.sales', $this->buildDashboardData($request->user()->id));
    }

    private function buildDashboardData(?int $userId = null): array
    {
        $customers = Customer::query();
        $leads = Lead::query();
        $followUps = FollowUp::with(['customer', 'lead', 'user'])->orderBy('due_date');
        $activities = Activity::with(['customer', 'lead', 'user'])->latest('activity_date');

        if ($userId !== null) {
            $customers->where('assigned_user_id', $userId);
            $leads->where('assigned_user_id', $userId);
            $followUps->where('user_id', $userId);
            $activities->where('user_id', $userId);
        }

        return [
            'data' => [
                'totalCustomers' => $customers->count(),
                'totalActiveLeads' => (clone $leads)->whereNotIn('status', ['won', 'lost', 'converted'])->count(),
                'completedFollowUps' => (clone $followUps)->where('status', 'completed')->count(),
                'recentActivities' => $activities->limit(5)->get(),
                'upcomingFollowUps' => (clone $followUps)
                    ->where('status', 'pending')
                    ->whereDate('due_date', '>=', now()->toDateString())
                    ->limit(5)
                    ->get(),
            ],
        ];
    }
}
