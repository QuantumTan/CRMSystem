<?php

namespace App\Http\Middleware;

use App\Models\SystemConfiguration;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class LoadSystemConfiguration
{
    public function handle(Request $request, Closure $next): Response
    {
        View::share('systemConfiguration', null);

        try {
            if (! Schema::hasTable('system_configurations')) {
                return $next($request);
            }

            $configuration = SystemConfiguration::current();

            config([
                'app.name' => $configuration->app_name,
                'auth.passwords.users.expire' => $configuration->password_reset_expire_minutes,
            ]);

            View::share('systemConfiguration', $configuration);
        } catch (\Throwable) {
            // Keep requests working even during migrations or partial setup.
        }

        return $next($request);
    }
}
