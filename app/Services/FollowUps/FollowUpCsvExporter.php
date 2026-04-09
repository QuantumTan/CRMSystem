<?php

namespace App\Services\FollowUps;

use App\Models\FollowUp;
use Illuminate\Support\Collection;

class FollowUpCsvExporter
{
    public function build(Collection $followUps): array
    {
        $rows = [
            ['Title', 'Description', 'Due Date', 'Status', 'Customer', 'Lead', 'Assigned To'],
        ];

        foreach ($followUps as $followUp) {
            /** @var FollowUp $followUp */
            $rows[] = [
                (string) $followUp->title,
                (string) ($followUp->description ?? ''),
                (string) optional($followUp->due_date)->format('Y-m-d'),
                (string) $followUp->status,
                (string) ($followUp->customer ? trim($followUp->customer->first_name.' '.$followUp->customer->last_name) : ''),
                (string) ($followUp->lead?->name ?? ''),
                (string) ($followUp->user?->name ?? ''),
            ];
        }

        return $rows;
    }
}
