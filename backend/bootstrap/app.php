<?php

/**
 * Cấu hình chính của ứng dụng Laravel.
 * Định nghĩa routes, middlewares, và xử lý ngoại lệ (exception handlers).
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
        // Load API route files với prefix 'api' và middleware group 'api'
        // QUAN TRỌNG: Phải có middleware 'api' để SubstituteBindings hoạt động
        // (nếu không có → Route Model Binding lỗi → Policy check fail → 403)
        then: function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/apis/auth.php'));
            
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/apis/leaveApplication.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Đăng ký alias cho custom middleware
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // API request không có token → trả JSON 401 thay vì redirect sang /login
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }
            return '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // === 401 - Chưa xác thực ===
        
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa xác thực. Vui lòng đăng nhập. / Unauthenticated. Please login.',
                'data' => null,
            ], 401);
        });

        $exceptions->render(function (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token đã hết hạn. Vui lòng làm mới hoặc đăng nhập lại. / Token expired.',
                'data' => null,
            ], 401);
        });

        $exceptions->render(function (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ. / Invalid token.',
                'data' => null,
            ], 401);
        });

        $exceptions->render(function (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực token. / Token authentication error.',
                'data' => null,
            ], 401);
        });

        // === 403 - Không có quyền ===
        
        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Không có quyền truy cập. / Access denied.',
                'data' => null,
            ], 403);
        });

        // === 404 - Không tìm thấy ===

        $exceptions->render(function (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tồn tại. / Not Found.',
                'data' => null,
            ], 404);
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tồn tại. / Not Found.',
                'data' => null,
            ], 404);
        });

        // === 422 - Dữ liệu không hợp lệ ===

        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ. / Validation failed.',
                'data' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (UnprocessableEntityHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không thể xử lý. / Unprocessable Entity.',
                'data' => null,
            ], 422);
        });
    })->create();
