<?php

if (!function_exists('getStatusColor')) {
    function getStatusColor(string $status): string
    {
        return match ($status) {
            'New'=> '#6c757d',
            'Contacted'=> '#0d6efd',
            'Qualified'=> '#198754',
            'Proposal Sent' => '#0dcaf0',
            'Negotiation'=> '#fd7e14',
            'Won'=> '#28a745',
            'Lost'=> '#dc3545',
            default=> '#6c757d',
        };
    }
}

if (!function_exists('getPriorityColor')) {
    function getPriorityColor(string $priority): string
    {
        return match ($priority) {
            'High'=> '#dc3545',
            'Medium' => '#fd7e14',
            'Low'=> '#28a745',
            default=> '#6c757d',
        };
    }
}
