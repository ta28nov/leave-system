<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\LeaveApplication;
use App\Policies\LeaveApplicationPolicy;

/** Đăng ký Policy phân quyền cho các Model */
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        LeaveApplication::class => LeaveApplicationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        Gate::policy(LeaveApplication::class, LeaveApplicationPolicy::class);
    }
}
