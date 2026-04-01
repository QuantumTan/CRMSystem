<?php

if (! function_exists('getStatusColor')) {
    function getStatusColor(string $status): string
    {
        $normalizedStatus = strtolower(str_replace('-', ' ', trim($status)));

        return match ($normalizedStatus) {
            'new' => '#2563eb',
            'contacted' => '#0891b2',
            'qualified' => '#16a34a',
            'proposal sent', 'proposal_sent' => '#d97706',
            'negotiation' => '#ea580c',
            'won' => '#15803d',
            'lost' => '#dc2626',
            default => '#2563eb',
        };
    }
}

if (! function_exists('getPriorityColor')) {
    function getPriorityColor(string $priority): string
    {
        $normalizedPriority = strtolower(trim($priority));

        return match ($normalizedPriority) {
            'high' => '#dc2626',
            'medium' => '#d97706',
            'low' => '#16a34a',
            default => '#2563eb',
        };
    }
}
