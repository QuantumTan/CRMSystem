<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'name',
        'email',
        'phone',
        'source',
        'status',
        'priority',
        'expected_value',
        'notes',
        'assigned_user_id',
        'converted_to_customer_id',
        'converted_at',
        'lost_reason',
        'lost_category',
        'lost_at',
    ];

    protected function casts(): array
    {
        return [
            'expected_value' => 'decimal:2',
            'converted_at' => 'datetime',
            'lost_at' => 'datetime',
        ];
    }

    // auto-generate lead_id when creating
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('sales_visibility', function (Builder $query): void {
            $user = Auth::user();

            if ($user instanceof User && $user->hasRole('sales')) {
                $query->where('assigned_user_id', $user->id);
            }
        });

        static::creating(function ($lead) {
            if (! $lead->lead_id) {
                $latest = self::latest('id')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                $lead->lead_id = 'LEAD-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // relationships
    // lead converted to customer
    public function convertedToCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_to_customer_id')->withTrashed();
    }

    // staff member assigned to the lead
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    // activities related to the leads
    public function activities()
    {
        return $this->hasMany(Activity::class, 'lead_id');
    }

    // follow-ups related to the lead
    public function followUps()
    {
        return $this->hasMany(FollowUp::class, 'lead_id');
    }

    // helper metods

    // get the possible statuses
    public static function getStatuses(): array
    {
        return [
            'new',
            'contacted',
            'qualified',
            'proposal_sent',
            'negotiation',
            'won',
            'lost',
        ];
    }

    // get all possible priorities
    public static function getPriorities(): array
    {
        return [
            'low',
            'medium',
            'high',
            'critical',
        ];
    }

    // possible lost cate
    public static function getLostCategories(): array
    {
        return [
            'budget' => 'Budget too high',
            'competitor' => 'Chose competitor',
            'timing' => 'Wrong timing',
            'not_interested' => 'Not interested',
            'no_decision' => 'No decision maker',
            'other' => 'Other',
        ];
    }

    // status check
    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    public function isContacted(): bool
    {
        return $this->status === 'contacted';
    }

    public function isQualified(): bool
    {
        return $this->status === 'qualified';
    }

    public function isProposalSent(): bool
    {
        return $this->status === 'proposal_sent';
    }

    public function isNegotiation(): bool
    {
        return $this->status === 'negotiation';
    }

    public function isWon(): bool
    {
        return $this->status === 'won';
    }

    public function isLost(): bool
    {
        return $this->status === 'lost';
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, ['won', 'lost']);
    }

    // CONVERSION METHODS

    // check if lead has been converted to the customer
    public function isConverted(): bool
    {
        return ! is_null($this->converted_to_customer_id);
    }

    // convert lead to customer
    public function convertToCustomer(): Customer
    {
        // prevention of duplucation
        if ($this->isConverted()) {
            throw new \Exception(('This lead has already been converted to a customer.'));
        }

        // Only won leads can be converted
        if (! $this->isWon()) {
            throw new \Exception('Only leads with "Won" status can be converted to customers.');
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            $customerPayload = [
                'first_name' => $this->extractFirstName($this->name),
                'last_name' => $this->extractLastName($this->name),
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => 'active',
                'assigned_user_id' => $this->assigned_user_id,
            ];

            // Ensure the assigned sales owner can access converted customers immediately.
            if ($this->assigned_user_id !== null) {
                $customerPayload['assignment_status'] = 'approved';
                $customerPayload['assignment_reviewed_by'] = Auth::id();
                $customerPayload['assignment_reviewed_at'] = now();
            }

            // Create customer from lead data
            $customer = Customer::create($customerPayload);

            // Update lead with conversion info
            $this->update([
                'converted_to_customer_id' => $customer->id,
                'converted_at' => now(),
            ]);

            // Optional: Move activities to customer
            $this->activities()->update([
                'customer_id' => $customer->id,
                'lead_id' => null,
            ]);

            // Optional: Move follow-ups to customer
            $this->followUps()->update([
                'customer_id' => $customer->id,
                'lead_id' => null,
            ]);

            DB::commit();

            return $customer;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    // LOST LEAD METHODS

    // mark lead as lost
    public function markAsLost(string $reason, ?string $category = null): void
    {
        $this->update([
            'status' => 'lost',
            'lost_reason' => $reason,
            'lost_category' => $category,
            'lost_at' => now(),
        ]);
    }

    // reopen lost
    public function reopen(string $newStatus = 'contacted'): void
    {
        if (! $this->isLost()) {
            throw new \Exception('Only lost leads can be reopened.');
        }

        $this->update([
            'status' => $newStatus,
            'lost_reason' => null,
            'lost_category' => null,
            'lost_at' => null,
        ]);
    }

    // SCOPES FOR QUERYING

    /**
     * Scope to get only active leads (not won or lost)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['won', 'lost']);
    }

    /**
     * Scope to get only won leads
     */
    public function scopeWon($query)
    {
        return $query->where('status', 'won');
    }

    /**
     * Scope to get only lost leads
     */
    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    /**
     * Scope to get leads by priority
     */
    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get leads by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get leads assigned to a specific user
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    // NAME EXTRACTION HELPERS

    /**
     * Extracts the first name from a full name string.
     */
    public function extractFirstName(?string $fullName): string
    {
        if (empty(trim($fullName))) {
            return 'Unknown';
        }

        $parts = explode(' ', trim($fullName));

        return $parts[0];
    }

    /**
     * Extracts the last name from a full name string.
     */
    public function extractLastName(?string $fullName): string
    {
        if (empty(trim($fullName))) {
            return '';
        }

        $parts = explode(' ', trim($fullName));

        if (count($parts) > 1) {
            array_shift($parts); // Remove the first name from the array

            return implode(' ', $parts); // Combine the remaining parts
        }

        return ''; // Return empty string if no last name exists
    }
}
