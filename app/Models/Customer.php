<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
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
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function assignmentReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignment_reviewed_by');
    }
}
