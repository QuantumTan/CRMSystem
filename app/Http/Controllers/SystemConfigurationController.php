<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSystemConfigurationRequest;
use App\Models\SystemConfiguration;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemConfigurationController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'systemConfiguration' => SystemConfiguration::current(),
            'statusOptions' => LeadService::STATUS_OPTIONS,
            'priorityOptions' => LeadService::PRIORITY_OPTIONS,
        ]);
    }

    public function update(UpdateSystemConfigurationRequest $request): RedirectResponse
    {
        $configuration = SystemConfiguration::current();
        $configuration->update($request->validated());

        config([
            'app.name' => $configuration->app_name,
            'auth.passwords.users.expire' => $configuration->password_reset_expire_minutes,
        ]);

        return redirect()
            ->route('settings.index')
            ->with('success', 'System configuration updated successfully.');
    }
}
