<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAuthorized($request);

        $data = $this->buildReportData();

        return view('reports.index', compact('data'));
    }

    public function exportCsv(Request $request): Response
    {
        $this->ensureAuthorized($request);

        $data = $this->buildReportData();
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

        $data = $this->buildReportData();
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

    private function buildReportData(): array
    {
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

        return $data;
    }

    private function buildCsvContent(array $data): string
    {
        $rows = [
            ['Report', 'Value'],
            ['Generated At', now()->toDateTimeString()],
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
        $lines = [
            'CRM Reports Summary',
            'Generated At: '.now()->toDateTimeString(),
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
