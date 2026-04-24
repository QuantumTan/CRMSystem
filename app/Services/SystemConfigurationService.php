<?php

namespace App\Services;

use App\Models\SystemConfiguration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class SystemConfigurationService
{
    public function bootstrap(): ?SystemConfiguration
    {
        View::share('systemConfiguration', null);

        try {
            if (! Schema::hasTable('system_configurations')) {
                return null;
            }

            $configuration = SystemConfiguration::current();

            $this->apply($configuration);
            View::share('systemConfiguration', $configuration);

            return $configuration;
        } catch (\Throwable) {
            return null;
        }
    }

    public function apply(SystemConfiguration $configuration): void
    {
        $runtimeConfiguration = [
            'app.name' => $configuration->app_name,
            'auth.passwords.users.expire' => $configuration->password_reset_expire_minutes,
            'crm.company_email' => $configuration->company_email,
            'crm.company_phone' => $configuration->company_phone,
            'crm.company_address' => $configuration->company_address,
            'crm.default_lead_status' => $configuration->default_lead_status,
            'crm.default_lead_priority' => $configuration->default_lead_priority,
            'crm.currency_code' => $configuration->currency_code,
            'mail.from.name' => $configuration->app_name,
        ];

        if (filled($configuration->company_email)) {
            $runtimeConfiguration['mail.from.address'] = $configuration->company_email;
        }

        config($runtimeConfiguration);
    }
}
