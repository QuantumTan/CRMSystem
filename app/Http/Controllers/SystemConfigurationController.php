<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSystemConfigurationRequest;
use App\Models\SystemConfiguration;
use App\Services\LeadService;
use App\Services\SystemConfigurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemConfigurationController extends Controller
{
    public function __construct(private SystemConfigurationService $systemConfigurationService) {}

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

        $this->systemConfigurationService->apply($configuration);

        return redirect()
            ->route('settings.index')
            ->with('success', 'System configuration updated successfully.');
    }
}
