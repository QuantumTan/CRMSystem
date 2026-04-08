<?php

namespace App\Exports;

use App\Models\FollowUp;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FollowUpCsvExport implements FromCollection, WithCustomCsvSettings, WithHeadings, WithMapping
{
    public function __construct(private Collection $followUps) {}

    public function collection(): Collection
    {
        return $this->followUps;
    }

    public function headings(): array
    {
        return ['Title', 'Description', 'Due Date', 'Status', 'Customer', 'Lead', 'Assigned To'];
    }

    public function map($row): array
    {
        /** @var FollowUp $followUp */
        $followUp = $row;

        return [
            $followUp->title,
            $followUp->description,
            optional($followUp->due_date)->format('Y-m-d'),
            $followUp->status,
            $followUp->customer ? ($followUp->customer->first_name.' '.$followUp->customer->last_name) : '',
            $followUp->lead?->name,
            $followUp->user?->name,
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
        ];
    }
}
