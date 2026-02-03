<?php

/**
 * AuthServiceProvider
 * 
 * Service Provider de dang ky Policies.
 * Su dung Gate::policy() de dang ky thu cong.
 */

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\LeaveApplication;
use App\Policies\LeaveApplicationPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        LeaveApplication::class => LeaveApplicationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // TEST: Kiểm tra xem AuthServiceProvider có được gọi không
        // Nếu thấy dòng này khi restart server, nghĩa là Provider ĐÃ LOAD
        Log::info('AuthServiceProvider::boot() CALLED - Registering Policy');
        
        // CÁCH 1: Dùng $policies array (Laravel standard)
        $this->registerPolicies();
        
        // CÁCH 2: Đăng ký trực tiếp bằng Gate (Backup)
        // Laravel sẽ tự động resolve LeaveApplication model từ route parameter
        Gate::policy(LeaveApplication::class, LeaveApplicationPolicy::class);
    }
}
