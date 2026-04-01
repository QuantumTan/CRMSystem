<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->hasAnyRole('admin', 'manager'),
            403,
            'Unauthorized. Insufficient permissions.'
        );

        $leadStatuses = Lead::getStatuses();
        $leadStatusCounts = Lead::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $leadsByStatus = collect($leadStatuses)
            ->map(fn (string $status): array => [
                'status' => $status,
                'total' => (int) ($leadStatusCounts[$status] ?? 0),
            ]);

        $userActivity = User::query()
            ->leftJoin('activities', 'users.id', '=', 'activities.user_id')
            ->selectRaw('users.name, users.role, count(activities.id) as total_activities')
            ->groupBy('users.id', 'users.name', 'users.role')
            ->orderByDesc('total_activities')
            ->get();

        $totalLeads = Lead::count();
        $wonLeads = Lead::where('status', 'won')->count();
        $lostLeads = Lead::where('status', 'lost')->count();
        $activePipelineLeads = max($totalLeads - $wonLeads - $lostLeads, 0);

        $data = [
            'totalCustomers' => Customer::count(),
            'leadsByStatus' => $leadsByStatus,
            'salesPipelineSummary' => [
                'total_leads' => $totalLeads,
                'active_pipeline_leads' => $activePipelineLeads,
                'won_leads' => $wonLeads,
                'lost_leads' => $lostLeads,
                'total_expected_value' => (float) Lead::sum('expected_value'),
                'active_expected_value' => (float) Lead::whereNotIn('status', ['won', 'lost'])->sum('expected_value'),
            ],
            'userActivity' => $userActivity,
            'followUpCompletion' => [
                'completed' => FollowUp::where('status', 'completed')->count(),
                'pending' => FollowUp::where('status', 'pending')->count(),
                'overdue' => FollowUp::where('status', 'pending')->whereDate('due_date', '<', now()->toDateString())->count(),
            ],
            'activitiesByType' => Activity::query()
                ->selectRaw('activity_type, count(*) as total')
                ->groupBy('activity_type')
                ->orderByDesc('total')
                ->get(),
        ];

        $totalFollowUps = $data['followUpCompletion']['completed'] + $data['followUpCompletion']['pending'];
        $data['followUpCompletion']['completion_rate'] = $totalFollowUps > 0
            ? round(($data['followUpCompletion']['completed'] / $totalFollowUps) * 100, 2)
            : 0.0;

        return view('reports.index', compact('data'));
    }
}
