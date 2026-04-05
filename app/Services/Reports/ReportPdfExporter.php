<?php

namespace App\Services\Reports;

class ReportPdfExporter
{
    public function build(array $data): string
    {
        $dateRangeLabel = $this->resolveDateRangeLabel($data);

        $lines = [
            'CRM Reports Summary',
            'Generated At: ' . now()->toDateTimeString(),
            'Date Range: ' . $dateRangeLabel,
            '',
            'Total Customers: ' . $data['totalCustomers'],
            'Pipeline Leads: ' . $data['salesPipelineSummary']['active_pipeline_leads'],
            'Won Leads: ' . $data['salesPipelineSummary']['won_leads'],
            'Lost Leads: ' . $data['salesPipelineSummary']['lost_leads'],
            'Total Expected Value: ' . number_format((float) $data['salesPipelineSummary']['total_expected_value'], 2),
            'Active Pipeline Value: ' . number_format((float) $data['salesPipelineSummary']['active_expected_value'], 2),
            'Follow-up Completion Rate: ' . number_format((float) $data['followUpCompletion']['completion_rate'], 2) . '%',
            '',
            'Lead Status Breakdown',
        ];

        foreach ($data['leadsByStatus'] as $row) {
            $lines[] = ' - ' . str($row['status'])->replace('_', ' ')->title() . ': ' . $row['total'];
        }

        $lines[] = '';
        $lines[] = 'User Activity';

        foreach ($data['userActivity'] as $row) {
            $lines[] = ' - ' . $row->name . ' (' . $row->role . '): ' . $row->total_activities;
        }

        $lines[] = '';
        $lines[] = 'Follow-up Status';
        $lines[] = ' - Completed: ' . $data['followUpCompletion']['completed'];
        $lines[] = ' - Pending: ' . $data['followUpCompletion']['pending'];
        $lines[] = ' - Overdue: ' . $data['followUpCompletion']['overdue'];

        $lines[] = '';
        $lines[] = 'Activities By Type';

        foreach ($data['activitiesByType'] as $row) {
            $lines[] = ' - ' . str($row->activity_type)->replace('_', ' ')->title() . ': ' . $row->total;
        }

        return $this->generateSimplePdf($lines);
    }

    private function resolveDateRangeLabel(array $data): string
    {
        $from = $data['filters']['from'] ?? null;
        $to = $data['filters']['to'] ?? null;

        if (! $from && ! $to) {
            return 'All Dates';
        }

        return ($from ?? 'Start') . ' to ' . ($to ?? 'Now');
    }

    private function generateSimplePdf(array $lines): string
    {
        $contentLines = [];

        foreach (array_slice($lines, 0, 55) as $index => $line) {
            $escaped = str_replace(
                ['\\', '(', ')'],
                ['\\\\', '\\(', '\\)'],
                (string) $line
            );

            $y = 780 - ($index * 13);
            $contentLines[] = "1 0 0 1 40 {$y} Tm ({$escaped}) Tj";
        }

        $contentStream = "BT\n/F1 10 Tf\n" . implode("\n", $contentLines) . "\nET";
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