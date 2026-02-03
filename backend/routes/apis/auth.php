<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Authentication API Routes
|--------------------------------------------------------------------------
|
| Route prefix: /auth (trong bootstrap/app.php đã set prefix 'api')
| => Full path: /api/auth/...
|
| Endpoints :
| - POST /api/auth/register  - Đăng ký (public)
| - POST /api/auth/login     - Đăng nhập (public)
| - POST /api/auth/logout    - Đăng xuất (protected)
| - POST /api/auth/refresh   - Làm mới token (protected)
| - GET  /api/auth/me        - Lấy thông tin user (protected)
|
*/

Route::prefix('auth')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Public Routes (Không cần token)
    |--------------------------------------------------------------------------
    |
    | Các routes này ai cũng có thể truy cập.
    | Thường là register và login.
    |
    */
    
    // POST /api/auth/register - Đăng ký tài khoản mới
    Route::post('/register', [AuthController::class, 'register']);
    
    // POST /api/auth/login - Đăng nhập, nhận JWT token
    Route::post('/login', [AuthController::class, 'login']);
    
    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Cần token)
    |--------------------------------------------------------------------------
    |
    | Các routes này yêu cầu JWT token hợp lệ.
    | Middleware 'auth:api' sẽ:
    | 1. Check header "Authorization: Bearer {token}"
    | 2. Verify token signature và expiry
    | 3. Decode token và set user vào auth()->user()
    | 4. Nếu token không hợp lệ → 401 Unauthorized
    |
    */
    
    Route::middleware('auth:api')->group(function () {
        // POST /api/auth/logout - Dang xuat, invalidate token
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // POST /api/auth/refresh - Lam moi token
        Route::post('/refresh', [AuthController::class, 'refresh']);
        
        // GET /api/auth/me - Lay thong tin user tu token
        Route::get('/me', [AuthController::class, 'me']);
        
        // DEBUG: Check user type
        Route::get('/debug', function () {
            $user = auth('api')->user();
            return response()->json([
                'user_id' => $user->id,
                'user_type' => $user->type,
                'user_type_gettype' => gettype($user->type),
                'is_admin' => (int) $user->type === \App\Enums\UserType::ADMIN->value,
                'is_manager' => (int) $user->type === \App\Enums\UserType::MANAGER->value,
                'is_employee' => (int) $user->type === \App\Enums\UserType::EMPLOYEE->value,
                'admin_value' => \App\Enums\UserType::ADMIN->value,
                'manager_value' => \App\Enums\UserType::MANAGER->value,
            ]);
        });
    });
});
