<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\LeaveApplication;
use App\Policies\LeaveApplicationPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Đăng ký Policy cho LeaveApplication
        Gate::policy(LeaveApplication::class, LeaveApplicationPolicy::class);

        // Route Model Binding: {leaveApplication} tự động resolve thành Model
        // (cần đăng ký thủ công vì dùng string ID thay vì auto-increment)
        \Illuminate\Support\Facades\Route::model('leaveApplication', LeaveApplication::class);
    }
}
