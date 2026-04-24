<?php

namespace App\Services\Reports;

class ReportCsvExporter
{
    public function build(array $data): array
    {
        $dateRangeLabel = $this->resolveDateRangeLabel($data);
        $currencyCode = config('crm.currency_code', 'PHP');

        $rows = [
            ['Report', 'Value'],
            ['Workspace', config('app.name', 'NexLink CRM')],
            ['Generated At', now()->toDateTimeString()],
            ['Date Range', $dateRangeLabel],
            ['Currency', $currencyCode],
            ['Total Customers', (string) $data['totalCustomers']],
            ['Pipeline Leads', (string) $data['salesPipelineSummary']['active_pipeline_leads']],
            ['Won Leads', (string) $data['salesPipelineSummary']['won_leads']],
            ['Lost Leads', (string) $data['salesPipelineSummary']['lost_leads']],
            ['Total Expected Value', $currencyCode.' '.number_format((float) $data['salesPipelineSummary']['total_expected_value'], 2, '.', '')],
            ['Active Pipeline Value', $currencyCode.' '.number_format((float) $data['salesPipelineSummary']['active_expected_value'], 2, '.', '')],
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
            $rows[] = [
                (string) $row->name,
                (string) $row->role,
                (string) $row->total_activities,
            ];
        }

        $rows[] = [];
        $rows[] = ['Follow-up Status', 'Count'];
        $rows[] = ['Completed', (string) $data['followUpCompletion']['completed']];
        $rows[] = ['Pending', (string) $data['followUpCompletion']['pending']];
        $rows[] = ['Overdue', (string) $data['followUpCompletion']['overdue']];

        $rows[] = [];
        $rows[] = ['Activity Type', 'Count'];

        foreach ($data['activitiesByType'] as $row) {
            $rows[] = [
                (string) $row->activity_type,
                (string) $row->total,
            ];
        }

        return $rows;
    }

    private function resolveDateRangeLabel(array $data): string
    {
        $from = $data['filters']['from'] ?? null;
        $to = $data['filters']['to'] ?? null;

        if (! $from && ! $to) {
            return 'All Dates';
        }

        return ($from ?? 'Start').' to '.($to ?? 'Now');
    }
}
