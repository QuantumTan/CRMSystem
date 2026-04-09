<?php

namespace App\Services\FollowUps;

use App\Models\FollowUp;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection;

class FollowUpPdfExporter
{
    public function build(Collection $followUps): string
    {
        $grouped = $followUps->groupBy(function (FollowUp $followUp): string {
            return strtolower((string) $followUp->status);
        });

        $orderedStatuses = collect(['pending', 'completed'])
            ->merge($grouped->keys()->diff(['pending', 'completed']))
            ->filter(fn (string $status): bool => $grouped->has($status));

        $sectionsHtml = '';

        foreach ($orderedStatuses as $status) {
            $sectionsHtml .= $this->renderStatusTable((string) $status, $grouped->get((string) $status, collect()));
        }

        if ($sectionsHtml === '') {
            $sectionsHtml = '<p class="empty">No follow-ups found for the selected filters.</p>';
        }

        $html = $this->renderDocument($sectionsHtml);

        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    private function renderDocument(string $sectionsHtml): string
    {
        $generatedAt = $this->escape(now()->format('Y-m-d H:i:s'));

        return '<!doctype html>'
            .'<html><head><meta charset="UTF-8"><style>'
            .'body{font-family:DejaVu Sans,sans-serif;color:#111827;font-size:12px;}'
            .'h1{margin:0 0 4px 0;font-size:20px;}'
            .'h2{margin:20px 0 8px 0;font-size:14px;color:#1f2937;}'
            .'.meta{margin:0 0 12px 0;color:#4b5563;font-size:11px;}'
            .'table{width:100%;border-collapse:collapse;margin:0 0 10px 0;}'
            .'th,td{border:1px solid #d1d5db;padding:6px;text-align:left;vertical-align:top;}'
            .'th{background:#f3f4f6;font-weight:700;}'
            .'.empty{padding:10px;border:1px solid #d1d5db;background:#f9fafb;}'
            .'</style></head><body>'
            .'<h1>Follow-ups Report</h1>'
            .'<p class="meta">Generated At: '.$generatedAt.'</p>'
            .$sectionsHtml
            .'</body></html>';
    }

    private function renderStatusTable(string $status, Collection $followUps): string
    {
        $rowsHtml = '';

        foreach ($followUps as $followUp) {
            /** @var FollowUp $followUp */
            $rowsHtml .= '<tr>'
                .'<td>'.$this->escape((string) $followUp->title).'</td>'
                .'<td>'.$this->escape((string) ($followUp->description ?? '')).'</td>'
                .'<td>'.$this->escape((string) optional($followUp->due_date)->format('Y-m-d')).'</td>'
                .'<td>'.$this->escape((string) ($followUp->customer ? trim($followUp->customer->first_name.' '.$followUp->customer->last_name) : 'N/A')).'</td>'
                .'<td>'.$this->escape((string) ($followUp->lead?->name ?? 'N/A')).'</td>'
                .'<td>'.$this->escape((string) ($followUp->user?->name ?? 'N/A')).'</td>'
                .'</tr>';
        }

        if ($rowsHtml === '') {
            $rowsHtml = '<tr><td colspan="6">No records in this section.</td></tr>';
        }

        return '<h2>Status: '.$this->escape(ucfirst($status)).' ('.$followUps->count().')</h2>'
            .'<table><thead><tr>'
            .'<th>Title</th><th>Description</th><th>Due Date</th><th>Customer</th><th>Lead</th><th>Assigned To</th>'
            .'</tr></thead><tbody>'
            .$rowsHtml
            .'</tbody></table>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
