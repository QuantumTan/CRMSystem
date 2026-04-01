<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'lead_id', 'user_id', 'activity_type', 'description', 'activity_date'];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->activity_type) {
            'call' => 'Call',
            'email' => 'Email',
            'meeting' => 'Meeting',
            'note' => 'Note',
            default => ucfirst($this->activity_type),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->activity_type) {
            'call' => '#198754', // green
            'email' => '#0d6efd', // blue
            'meeting' => '#ffc107', // yellow
            'note' => '#8b5cf6', // purple
            default => '#6c757d', // gray
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->activity_type) {
            'call' => 'bi-telephone-fill',
            'email' => 'bi-envelope-fill',
            'meeting' => 'bi-calendar-event-fill',
            'note' => 'bi-sticky-fill',
            default => 'bi-activity',
        };
    }
}
