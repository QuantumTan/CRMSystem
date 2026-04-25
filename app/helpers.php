<?php

if (! function_exists('getStatusColor')) {
    function getStatusColor(string $status): string
    {
        $normalizedStatus = strtolower(str_replace('-', ' ', trim($status)));

        return match ($normalizedStatus) {
            'new' => 'var(--badge-info-dot)',
            'contacted' => 'var(--badge-purple-dot)',
            'qualified' => 'var(--badge-success-dot)',
            'proposal sent', 'proposal_sent' => 'var(--badge-orange-dot)',
            'negotiation' => 'var(--badge-warning-dot)',
            'won' => 'var(--badge-success-dot)',
            'lost' => 'var(--badge-danger-dot)',
            default => 'var(--badge-neutral-dot)',
        };
    }
}

if (! function_exists('getPriorityColor')) {
    function getPriorityColor(string $priority): string
    {
        $normalizedPriority = strtolower(trim($priority));

        return match ($normalizedPriority) {
            'high', 'critical' => 'var(--badge-danger-dot)',
            'medium' => 'var(--badge-warning-dot)',
            'low' => 'var(--badge-success-dot)',
            default => 'var(--badge-neutral-dot)',
        };
    }
}
