<?php

/**
 * Application Bootstrap Configuration
 * 
 * File cấu hình chính của Laravel application.
 * Định nghĩa routes, middlewares, và exception handlers.
 * 
 * Cấu hình:
 * - API Routes: /api/auth/*, /api/leave-applications/*
 * - Middleware: role (CheckRole), auth:api (JWT)
 * - Exception Handlers: 401, 403, 404, 422
 * 
 * @see config/auth.php - JWT guard configuration
 * @see routes/apis/ - API route files
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        /*
        |--------------------------------------------------------------------------
        | API Routes Registration
        |--------------------------------------------------------------------------
        |
        | Sử dụng callback 'then' để load nhiều route files cho API.
        | Tất cả routes trong callback sẽ có prefix 'api'.
        |
        | CRITICAL: Phải áp dụng middleware group 'api' để có SubstituteBindings!
        | Nếu không có SubstituteBindings → Route Model Binding không hoạt động
        | → {leaveApplication} vẫn là string thay vì Model → Policy check fail → 403
        |
        */
        then: function () {
            // Auth routes: /api/auth/...
            // MUST apply 'api' middleware group for SubstituteBindings to work!
            Route::prefix('api')
                ->middleware('api')  // ← FIX: Thêm middleware group 'api'
                ->group(base_path('routes/apis/auth.php'));
            
            // Leave Application routes: /api/leave-applications/...
            // MUST apply 'api' middleware group for SubstituteBindings to work!
            Route::prefix('api')
                ->middleware('api')  // ← FIX: Thêm middleware group 'api'
                ->group(base_path('routes/apis/leaveApplication.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        |
        | Đăng ký alias cho custom middlewares.
        | Sử dụng: Route::middleware('role:admin,manager')
        |
        */
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Redirect When Unauthenticated
        |--------------------------------------------------------------------------
        |
        | Khi API request không có token hoặc token không hợp lệ,
        | thay vì redirect sang route 'login' (gây lỗi), 
        | return JSON response 401 Unauthorized.
        |
        */
        $middleware->redirectGuestsTo(function ($request) {
            // Nếu là API request (Accept: application/json hoặc URL bắt đầu bằng /api)
            // thì return null để không redirect, trigger AuthenticationException
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }
            
            // Web requests redirect sang trang login (nếu có)
            return '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
        |--------------------------------------------------------------------------
        | Authentication Exceptions (401 Unauthorized)
        |--------------------------------------------------------------------------
        |
        | Xử lý các lỗi liên quan đến JWT Authentication:
        | - AuthenticationException: Chưa đăng nhập / Token không có
        | - TokenExpiredException: Token đã hết hạn
        | - TokenInvalidException: Token không hợp lệ
        | - JWTException: Lỗi JWT chung
        |
        */
        
        // AuthenticationException → 401 (Chưa đăng nhập)
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa xác thực. Vui lòng đăng nhập. / Unauthenticated. Please login.',
                'data' => null,
            ], 401);
        });

        // TokenExpiredException → 401 (Token hết hạn)
        $exceptions->render(function (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token đã hết hạn. Vui lòng làm mới hoặc đăng nhập lại. / Token expired. Please refresh or login again.',
                'data' => null,
            ], 401);
        });

        // TokenInvalidException → 401 (Token không hợp lệ)
        $exceptions->render(function (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ. / Invalid token.',
                'data' => null,
            ], 401);
        });

        // JWTException → 401 (Lỗi JWT chung)
        $exceptions->render(function (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực token. / Token authentication error.',
                'data' => null,
            ], 401);
        });

        /*
        |--------------------------------------------------------------------------
        | Authorization Exceptions (403 Forbidden)
        |--------------------------------------------------------------------------
        |
        | Xử lý các lỗi liên quan đến Authorization:
        | - AccessDeniedHttpException: Không có quyền truy cập
        |
        */
        
        // AccessDeniedHttpException → 403 (Không có quyền)
        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Không có quyền truy cập. / Access denied.',
                'data' => null,
            ], 403);
        });

        /*
        |--------------------------------------------------------------------------
        | Not Found Exceptions (404)
        |--------------------------------------------------------------------------
        */

        // ModelNotFoundException → 404
        $exceptions->render(function (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tồn tại. / Not Found.',
                'data' => null,
            ], 404);
        });

        // NotFoundHttpException → 404
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tồn tại. / Not Found.',
                'data' => null,
            ], 404);
        });

        /*
        |--------------------------------------------------------------------------
        | Validation Exceptions (422)
        |--------------------------------------------------------------------------
        */

        // ValidationException → 422
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ. / Validation failed.',
                'data' => $e->errors(),
            ], 422);
        });

        // UnprocessableEntityHttpException → 422
        $exceptions->render(function (UnprocessableEntityHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không thể xử lý. / Unprocessable Entity.',
                'data' => null,
            ], 422);
        });
    })->create();

