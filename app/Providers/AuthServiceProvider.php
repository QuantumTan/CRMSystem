<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\FollowUpPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        FollowUp::class => FollowUpPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
