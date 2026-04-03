<?php

namespace App\Models;

use App\Filters\ActivityFilter;
use App\Models\Scopes\ActivityVisibilityScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'lead_id',
        'user_id',
        'activity_type',
        'description',
        'activity_date',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilter(Builder $query, ActivityFilter $filter): Builder
    {
        return $filter->apply($query);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return ActivityVisibilityScope::apply($query, $user);
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
            'call' => '#198754',
            'email' => '#0d6efd',
            'meeting' => '#ffc107',
            'note' => '#8b5cf6',
            default => '#6c757d',
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