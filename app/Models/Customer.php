<?php

namespace App\Models;

use App\Models\Scopes\CustomerVisibilityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'address',
        'status',
        'assigned_user_id',
        'assignment_status',
        'assignment_reviewed_by',
        'assignment_reviewed_at',
    ];

    protected $casts = [
        'assignment_reviewed_at' => 'datetime',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id')->withTrashed();
    }

    public function assignmentReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignment_reviewed_by')->withTrashed();
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new CustomerVisibilityScope);
    }

    public function approve(User $reviewer): void
    {
        $this->update([
            'assignment_status' => 'approved',
            'assignment_reviewed_by' => $reviewer->id,
            'assignment_reviewed_at' => now(),
        ]);
    }

    public function reject(User $reviewer): void
    {
        $this->update([
            'assignment_status' => 'rejected',
            'assignment_reviewed_by' => $reviewer->id,
            'assignment_reviewed_at' => now(),
        ]);
    }

    public function reassignTo(int $userId): void
    {
        $this->update([
            'assigned_user_id' => $userId,
            'assignment_status' => 'pending',
            'assignment_reviewed_by' => null,
            'assignment_reviewed_at' => null,
        ]);
    }
}
