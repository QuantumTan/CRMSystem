<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    protected $fillable = [
        'app_name',
        'company_email',
        'company_phone',
        'company_address',
        'default_lead_status',
        'default_lead_priority',
        'currency_code',
        'password_reset_expire_minutes',
    ];

    public static function defaults(): array
    {
        return [
            'app_name' => config('app.name', 'NexLink CRM'),
            'company_email' => config('crm.company_email'),
            'company_phone' => config('crm.company_phone'),
            'company_address' => config('crm.company_address'),
            'default_lead_status' => config('crm.default_lead_status', 'new'),
            'default_lead_priority' => config('crm.default_lead_priority', 'medium'),
            'currency_code' => config('crm.currency_code', 'PHP'),
            'password_reset_expire_minutes' => 60,
        ];
    }

    public static function current(): self
    {
        $configuration = static::query()->first();

        if ($configuration instanceof self) {
            return $configuration;
        }

        return static::query()->create(static::defaults());
    }

    protected function casts(): array
    {
        return [
            'password_reset_expire_minutes' => 'integer',
        ];
    }
}
