<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class FollowUpCsvExport implements FromArray, WithCustomCsvSettings
{
    public function __construct(private array $rows) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
        ];
    }
}
