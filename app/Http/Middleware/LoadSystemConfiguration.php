<?php

namespace App\Http\Middleware;

use App\Services\SystemConfigurationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoadSystemConfiguration
{
    public function __construct(private SystemConfigurationService $systemConfigurationService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->systemConfigurationService->bootstrap();

        return $next($request);
    }
}
