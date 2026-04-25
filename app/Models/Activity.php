<?php

namespace App\Models;

use App\Filters\ActivityFilter;
use App\Models\Scopes\ActivityVisibilityScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

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
        return $this->belongsTo(Lead::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
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
            'call' => 'var(--badge-info-dot)',
            'email' => 'var(--badge-indigo-dot)',
            'meeting' => 'var(--badge-warning-dot)',
            'note' => 'var(--badge-neutral-dot)',
            default => 'var(--badge-neutral-dot)',
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
