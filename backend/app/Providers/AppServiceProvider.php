<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\LeaveApplication;
use App\Policies\LeaveApplicationPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        // Đăng ký Policy cho LeaveApplication
        Gate::policy(LeaveApplication::class, LeaveApplicationPolicy::class);

        // Explicit Route Model Binding cho custom string ID
        // Laravel 11 không tự nhận diện String ID khi tên param khác convention
        // Route parameter {leaveApplication} sẽ tự động resolve thành LeaveApplication Model
        // Tìm kiếm theo primary key: LeaveApplication::where('id', $value)->firstOrFail()
        \Illuminate\Support\Facades\Route::model('leaveApplication', LeaveApplication::class);
    }
}
