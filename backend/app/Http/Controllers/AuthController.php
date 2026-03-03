<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Helpers\ResponseHelper;

/**
 * Controller cho Authentication APIs.
 * Controller chỉ nhận Request, gọi Service xử lý, và trả về Response.
 */
class AuthController extends Controller
{
    protected $auth_service;

    public function __construct(AuthService $auth_service)
    {
        $this->auth_service = $auth_service;
    }

    /** Đăng ký tài khoản mới (công khai, không cần token) */
    public function register(RegisterRequest $request)
    {
        $token_data = $this->auth_service->register($request->validated());

        return ResponseHelper::success(
            'Đăng ký thành công. / Registration successful.',
            $token_data,
            201
        );
    }

    /** Đăng nhập và nhận JWT token (công khai, không cần token) */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $token_data = $this->auth_service->login($credentials);

        if (!$token_data) {
            return ResponseHelper::error(
                'Thông tin đăng nhập không chính xác. / Invalid credentials.',
                null,
                401
            );
        }

        return ResponseHelper::success(
            'Đăng nhập thành công. / Login successful.',
            $token_data
        );
    }

    /** Đăng xuất — vô hiệu hóa token hiện tại */
    public function logout()
    {
        $this->auth_service->logout();

        return ResponseHelper::success(
            'Đăng xuất thành công. / Logout successful.',
            null
        );
    }

    /** Làm mới token */
    public function refresh()
    {
        $token_data = $this->auth_service->refresh();

        return ResponseHelper::success(
            'Làm mới token thành công. / Token refreshed successfully.',
            $token_data
        );
    }

    /** Lấy thông tin user hiện tại từ token */
    public function me()
    {
        $user = $this->auth_service->me();

        return ResponseHelper::success(
            'Lấy thông tin thành công. / User info retrieved successfully.',
            $user
        );
    }
}
