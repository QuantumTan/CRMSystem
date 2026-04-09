<?php

namespace App\Services\Reports;

use Dompdf\Dompdf;
use Dompdf\Options;

class ReportPdfExporter
{
    public function build(array $data): string
    {
        $dateRangeLabel = $this->resolveDateRangeLabel($data);

        $summaryRows = [
            ['Metric', 'Value'],
            ['Total Customers', (string) ($data['totalCustomers'] ?? 0)],
            ['Pipeline Leads', (string) ($data['salesPipelineSummary']['active_pipeline_leads'] ?? 0)],
            ['Won Leads', (string) ($data['salesPipelineSummary']['won_leads'] ?? 0)],
            ['Lost Leads', (string) ($data['salesPipelineSummary']['lost_leads'] ?? 0)],
            ['Total Expected Value', number_format((float) ($data['salesPipelineSummary']['total_expected_value'] ?? 0), 2, '.', '')],
            ['Active Pipeline Value', number_format((float) ($data['salesPipelineSummary']['active_expected_value'] ?? 0), 2, '.', '')],
            ['Follow-up Completion Rate', number_format((float) ($data['followUpCompletion']['completion_rate'] ?? 0), 2, '.', '').'%'],
        ];

        $leadStatusRows = [['Status', 'Count']];
        foreach ($data['leadsByStatus'] ?? [] as $row) {
            $leadStatusRows[] = [
                (string) str((string) ($row['status'] ?? 'unknown'))->replace('_', ' ')->title(),
                (string) ($row['total'] ?? 0),
            ];
        }

        $userActivityRows = [['User', 'Role', 'Activities']];
        foreach ($data['userActivity'] ?? [] as $row) {
            $userActivityRows[] = [
                (string) ($row->name ?? 'N/A'),
                (string) ($row->role ?? 'N/A'),
                (string) ($row->total_activities ?? 0),
            ];
        }

        $followUpRows = [
            ['Status', 'Count'],
            ['Completed', (string) ($data['followUpCompletion']['completed'] ?? 0)],
            ['Pending', (string) ($data['followUpCompletion']['pending'] ?? 0)],
            ['Overdue', (string) ($data['followUpCompletion']['overdue'] ?? 0)],
        ];

        $activitiesRows = [['Activity Type', 'Count']];
        foreach ($data['activitiesByType'] ?? [] as $row) {
            $activitiesRows[] = [
                (string) str((string) ($row->activity_type ?? 'unknown'))->replace('_', ' ')->title(),
                (string) ($row->total ?? 0),
            ];
        }

        $html = $this->renderDocument([
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'dateRange' => $dateRangeLabel,
            'tables' => [
                ['title' => 'Summary', 'rows' => $summaryRows],
                ['title' => 'Lead Status Breakdown', 'rows' => $leadStatusRows],
                ['title' => 'User Activity', 'rows' => $userActivityRows],
                ['title' => 'Follow-up Status', 'rows' => $followUpRows],
                ['title' => 'Activities By Type', 'rows' => $activitiesRows],
            ],
        ]);

        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
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

    private function renderDocument(array $context): string
    {
        $sectionsHtml = '';

        foreach ($context['tables'] as $table) {
            $sectionsHtml .= '<section class="section">'
                .'<h2>'.$this->escape((string) $table['title']).'</h2>'
                .$this->renderTable($table['rows'])
                .'</section>';
        }

        return '<!doctype html>'
            .'<html><head><meta charset="UTF-8"><style>'
            .'body{font-family:DejaVu Sans,sans-serif;color:#111827;font-size:12px;}'
            .'h1{margin:0 0 4px 0;font-size:20px;}'
            .'h2{margin:18px 0 8px 0;font-size:14px;color:#1f2937;}'
            .'.meta{margin:0 0 12px 0;color:#4b5563;font-size:11px;}'
            .'.section{margin-bottom:8px;page-break-inside:avoid;}'
            .'table{width:100%;border-collapse:collapse;}'
            .'th,td{border:1px solid #d1d5db;padding:6px;text-align:left;vertical-align:top;}'
            .'th{background:#f3f4f6;font-weight:700;}'
            .'</style></head><body>'
            .'<h1>CRM Reports Summary</h1>'
            .'<p class="meta">Generated At: '.$this->escape((string) $context['generatedAt']).'<br>Date Range: '.$this->escape((string) $context['dateRange']).'</p>'
            .$sectionsHtml
            .'</body></html>';
    }

    private function renderTable(array $rows): string
    {
        if ($rows === [] || ! isset($rows[0]) || ! is_array($rows[0])) {
            return '<p>No data available.</p>';
        }

        $header = $rows[0];
        $body = array_slice($rows, 1);
        $headerHtml = '';
        foreach ($header as $cell) {
            $headerHtml .= '<th>'.$this->escape((string) $cell).'</th>';
        }

        $bodyHtml = '';
        foreach ($body as $row) {
            $bodyHtml .= '<tr>';
            foreach ($row as $cell) {
                $bodyHtml .= '<td>'.$this->escape((string) $cell).'</td>';
            }
            $bodyHtml .= '</tr>';
        }

        if ($bodyHtml === '') {
            $bodyHtml = '<tr><td colspan="'.count($header).'">No data available.</td></tr>';
        }

        return '<table><thead><tr>'.$headerHtml.'</tr></thead><tbody>'.$bodyHtml.'</tbody></table>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
