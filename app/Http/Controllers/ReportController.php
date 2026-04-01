<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAuthorized($request);
        $filters = $this->validatedDateRange($request);

        $data = $this->buildReportData($filters['from'], $filters['to'], $request->user());

        return view('reports.index', compact('data', 'filters'));
    }

    public function exportCsv(Request $request): Response
    {
        $this->ensureAuthorized($request);
        $filters = $this->validatedDateRange($request);

        $data = $this->buildReportData($filters['from'], $filters['to'], $request->user());
        $csv = $this->buildCsvContent($data);
        $fileName = 'reports-'.now()->format('Ymd-His').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $this->ensureAuthorized($request);
        $filters = $this->validatedDateRange($request);

        $data = $this->buildReportData($filters['from'], $filters['to'], $request->user());
        $pdf = $this->buildSimplePdf($data);
        $fileName = 'reports-'.now()->format('Ymd-His').'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    private function ensureAuthorized(Request $request): void
    {
        abort_unless(
            $request->user()?->hasAnyRole('admin', 'manager'),
            403,
            'Unauthorized. Insufficient permissions.'
        );
    }

    private function validatedDateRange(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
        ];
    }

    private function buildReportData(?string $fromDate = null, ?string $toDate = null, ?User $user = null): array
    {
        $isManager = $user?->hasRole('manager') ?? false;
        $salesUserIds = $isManager
            ? User::query()->where('role', 'sales')->pluck('id')->all()
            : [];

        $leadStatuses = Lead::getStatuses();
        $leadStatusCountsQuery = Lead::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status');

        if ($isManager) {
            $leadStatusCountsQuery->where(function (Builder $query) use ($salesUserIds): void {
                $query->whereIn('assigned_user_id', $salesUserIds)
                    ->orWhereNull('assigned_user_id');
            });
        }

        $this->applyDateRange($leadStatusCountsQuery, 'created_at', $fromDate, $toDate);
        $leadStatusCounts = $leadStatusCountsQuery->pluck('total', 'status');

        $leadsByStatus = collect($leadStatuses)
            ->map(fn (string $status): array => [
                'status' => $status,
                'total' => (int) ($leadStatusCounts[$status] ?? 0),
            ]);

        $activityTotalsByUser = Activity::query()
            ->selectRaw('user_id, count(*) as total_activities')
            ->groupBy('user_id');

        if ($isManager) {
            $activityTotalsByUser->whereIn('user_id', $salesUserIds);
        }

        $this->applyDateRange($activityTotalsByUser, 'activity_date', $fromDate, $toDate);

        $userActivity = User::query()
            ->leftJoinSub($activityTotalsByUser, 'activity_totals', function ($join): void {
                $join->on('users.id', '=', 'activity_totals.user_id');
            })
            ->selectRaw('users.name, users.role, COALESCE(activity_totals.total_activities, 0) as total_activities')
            ->when($isManager, fn (Builder $query) => $query->where('users.role', 'sales'))
            ->orderByDesc('total_activities')
            ->get();

        $leadBaseQuery = Lead::query();

        if ($isManager) {
            $leadBaseQuery->where(function (Builder $query) use ($salesUserIds): void {
                $query->whereIn('assigned_user_id', $salesUserIds)
                    ->orWhereNull('assigned_user_id');
            });
        }

        $this->applyDateRange($leadBaseQuery, 'created_at', $fromDate, $toDate);

        $totalLeads = (clone $leadBaseQuery)->count();
        $wonLeads = (clone $leadBaseQuery)->where('status', 'won')->count();
        $lostLeads = (clone $leadBaseQuery)->where('status', 'lost')->count();
        $activePipelineLeads = max($totalLeads - $wonLeads - $lostLeads, 0);

        $customerCountQuery = Customer::query();

        if ($isManager) {
            $customerCountQuery->where(function (Builder $query) use ($salesUserIds): void {
                $query->whereIn('assigned_user_id', $salesUserIds)
                    ->orWhereNull('assigned_user_id');
            });
        }

        $this->applyDateRange($customerCountQuery, 'created_at', $fromDate, $toDate);

        $totalExpectedValueQuery = Lead::query();

        if ($isManager) {
            $totalExpectedValueQuery->where(function (Builder $query) use ($salesUserIds): void {
                $query->whereIn('assigned_user_id', $salesUserIds)
                    ->orWhereNull('assigned_user_id');
            });
        }

        $this->applyDateRange($totalExpectedValueQuery, 'created_at', $fromDate, $toDate);

        $activeExpectedValueQuery = Lead::query()->whereNotIn('status', ['won', 'lost']);

        if ($isManager) {
            $activeExpectedValueQuery->where(function (Builder $query) use ($salesUserIds): void {
                $query->whereIn('assigned_user_id', $salesUserIds)
                    ->orWhereNull('assigned_user_id');
            });
        }

        $this->applyDateRange($activeExpectedValueQuery, 'created_at', $fromDate, $toDate);

        $completedFollowUpsQuery = FollowUp::query()->where('status', 'completed');

        if ($isManager) {
            $completedFollowUpsQuery->whereIn('user_id', $salesUserIds);
        }

        $this->applyDateRange($completedFollowUpsQuery, 'due_date', $fromDate, $toDate);

        $pendingFollowUpsQuery = FollowUp::query()->where('status', 'pending');

        if ($isManager) {
            $pendingFollowUpsQuery->whereIn('user_id', $salesUserIds);
        }

        $this->applyDateRange($pendingFollowUpsQuery, 'due_date', $fromDate, $toDate);

        $overdueFollowUpsQuery = FollowUp::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', now()->toDateString());

        if ($isManager) {
            $overdueFollowUpsQuery->whereIn('user_id', $salesUserIds);
        }

        $this->applyDateRange($overdueFollowUpsQuery, 'due_date', $fromDate, $toDate);

        $activitiesByTypeQuery = Activity::query()
            ->selectRaw('activity_type, count(*) as total')
            ->groupBy('activity_type')
            ->orderByDesc('total');

        if ($isManager) {
            $activitiesByTypeQuery->whereIn('user_id', $salesUserIds);
        }

        $this->applyDateRange($activitiesByTypeQuery, 'activity_date', $fromDate, $toDate);

        $data = [
            'totalCustomers' => $customerCountQuery->count(),
            'leadsByStatus' => $leadsByStatus,
            'salesPipelineSummary' => [
                'total_leads' => $totalLeads,
                'active_pipeline_leads' => $activePipelineLeads,
                'won_leads' => $wonLeads,
                'lost_leads' => $lostLeads,
                'total_expected_value' => (float) $totalExpectedValueQuery->sum('expected_value'),
                'active_expected_value' => (float) $activeExpectedValueQuery->sum('expected_value'),
            ],
            'userActivity' => $userActivity,
            'followUpCompletion' => [
                'completed' => $completedFollowUpsQuery->count(),
                'pending' => $pendingFollowUpsQuery->count(),
                'overdue' => $overdueFollowUpsQuery->count(),
            ],
            'activitiesByType' => $activitiesByTypeQuery->get(),
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

    private function applyDateRange(Builder $query, string $dateColumn, ?string $fromDate, ?string $toDate): void
    {
        if ($fromDate !== null) {
            $query->whereDate($dateColumn, '>=', $fromDate);
        }

        if ($toDate !== null) {
            $query->whereDate($dateColumn, '<=', $toDate);
        }
    }

    private function buildCsvContent(array $data): string
    {
        $dateRangeLabel = 'All Dates';
        if ($data['filters']['from'] || $data['filters']['to']) {
            $dateRangeLabel = ($data['filters']['from'] ?? 'Start').' to '.($data['filters']['to'] ?? 'Now');
        }

        $rows = [
            ['Report', 'Value'],
            ['Generated At', now()->toDateTimeString()],
            ['Date Range', $dateRangeLabel],
            ['Total Customers', (string) $data['totalCustomers']],
            ['Pipeline Leads', (string) $data['salesPipelineSummary']['active_pipeline_leads']],
            ['Won Leads', (string) $data['salesPipelineSummary']['won_leads']],
            ['Lost Leads', (string) $data['salesPipelineSummary']['lost_leads']],
            ['Total Expected Value', number_format((float) $data['salesPipelineSummary']['total_expected_value'], 2, '.', '')],
            ['Active Pipeline Value', number_format((float) $data['salesPipelineSummary']['active_expected_value'], 2, '.', '')],
            ['Follow-up Completion Rate', number_format((float) $data['followUpCompletion']['completion_rate'], 2, '.', '').'%'],
            [],
            ['Lead Status', 'Count'],
        ];

        foreach ($data['leadsByStatus'] as $row) {
            $rows[] = [(string) $row['status'], (string) $row['total']];
        }

        $rows[] = [];
        $rows[] = ['User', 'Role', 'Activities'];

        foreach ($data['userActivity'] as $row) {
            $rows[] = [(string) $row->name, (string) $row->role, (string) $row->total_activities];
        }

        $rows[] = [];
        $rows[] = ['Follow-up Status', 'Count'];
        $rows[] = ['Completed', (string) $data['followUpCompletion']['completed']];
        $rows[] = ['Pending', (string) $data['followUpCompletion']['pending']];
        $rows[] = ['Overdue', (string) $data['followUpCompletion']['overdue']];

        $stream = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return (string) $content;
    }

    private function buildSimplePdf(array $data): string
    {
        $dateRangeLabel = 'All Dates';
        if ($data['filters']['from'] || $data['filters']['to']) {
            $dateRangeLabel = ($data['filters']['from'] ?? 'Start').' to '.($data['filters']['to'] ?? 'Now');
        }

        $lines = [
            'CRM Reports Summary',
            'Generated At: '.now()->toDateTimeString(),
            'Date Range: '.$dateRangeLabel,
            '',
            'Total Customers: '.$data['totalCustomers'],
            'Pipeline Leads: '.$data['salesPipelineSummary']['active_pipeline_leads'],
            'Won Leads: '.$data['salesPipelineSummary']['won_leads'],
            'Lost Leads: '.$data['salesPipelineSummary']['lost_leads'],
            'Total Expected Value: '.number_format((float) $data['salesPipelineSummary']['total_expected_value'], 2),
            'Active Pipeline Value: '.number_format((float) $data['salesPipelineSummary']['active_expected_value'], 2),
            'Follow-up Completion Rate: '.number_format((float) $data['followUpCompletion']['completion_rate'], 2).'%',
            '',
            'Lead Status Breakdown',
        ];

        foreach ($data['leadsByStatus'] as $row) {
            $lines[] = ' - '.str($row['status'])->replace('_', ' ')->title().': '.$row['total'];
        }

        $lines[] = '';
        $lines[] = 'User Activity';

        foreach ($data['userActivity'] as $row) {
            $lines[] = ' - '.$row->name.' ('.$row->role.'): '.$row->total_activities;
        }

        $lines[] = '';
        $lines[] = 'Follow-up Status';
        $lines[] = ' - Completed: '.$data['followUpCompletion']['completed'];
        $lines[] = ' - Pending: '.$data['followUpCompletion']['pending'];
        $lines[] = ' - Overdue: '.$data['followUpCompletion']['overdue'];

        $contentLines = [];
        foreach (array_slice($lines, 0, 55) as $index => $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $y = 780 - ($index * 13);
            $contentLines[] = "1 0 0 1 40 {$y} Tm ({$escaped}) Tj";
        }

        $contentStream = "BT\n/F1 10 Tf\n".implode("\n", $contentLines)."\nET";
        $length = strlen($contentStream);

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Length {$length} >>\nstream\n{$contentStream}\nendstream\nendobj\n";
        $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}
