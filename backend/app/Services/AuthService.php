<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * AuthService
 * 
 * Service Layer chứa toàn bộ logic nghiệp vụ cho Authentication.
 * Controller CHỈ gọi các methods ở đây, KHÔNG viết logic trực tiếp.
 * 
 * Flow xác thực:
 * 1. Login: Nhận credentials → Xác thực → Tạo JWT token → Trả về token
 * 2. Register: Nhận data → Tạo user → Tạo JWT token → Trả về token
 * 3. Logout: Invalidate token hiện tại
 * 4. Refresh: Tạo token mới từ token cũ (chưa hết hạn)
 * 5. Me: Lấy thông tin user từ token
 */
class AuthService
{
    /*
    |--------------------------------------------------------------------------
    | Authentication Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Đăng ký user mới
     * 
     * Logic:
     * 1. Generate unique ID (char(10)) cho user mới
     * 2. Set default type = 2 (Employee) nếu không được cung cấp
     * 3. Password sẽ tự động hash nhờ $casts['password' => 'hashed'] trong User model
     * 4. Tạo JWT token cho user mới đăng ký
     * 
     * @param array $data - Gồm: name, email, password, type (optional)
     * @return array - Token info
     */
    public function register(array $data): array
    {
        // Generate unique ID dạng char(10)
        // Sử dụng strtoupper để đảm bảo consistent với LeaveApplication ID
        $data['id'] = strtoupper(Str::random(10));
        
        // Default type = 2 (Employee) theo tài liệu Section 4 (users table)
        // Admin/Manager chỉ được tạo bởi Admin trong hệ thống
        if (!isset($data['type'])) {
            $data['type'] = 2; // Employee
        }

        // Tạo user - password tự động hash nhờ $casts
        $user = User::create($data);

        // Tạo token cho user mới đăng ký
        // Sử dụng JWTAuth::fromUser() để tạo token từ user instance
        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token);
    }

    /**
     * Đăng nhập
     * 
     * Logic quan trọng:
     * - JWTAuth::attempt() sẽ:
     *   1. Tìm user theo email
     *   2. So sánh password (đã hash) với password được cung cấp
     *   3. Nếu khớp, tạo JWT token và trả về
     *   4. Nếu không khớp, trả về false
     * 
     * JWT Token structure:
     * - Header: Algorithm (HS256) + Token type (JWT)
     * - Payload: sub (user ID), iat (issued at), exp (expiry), custom claims
     * - Signature: HMACSHA256(header + payload, secret)
     * 
     * @param array $credentials - Gồm: email, password
     * @return array|null - Token info hoặc null nếu thất bại
     */
    public function login(array $credentials): ?array
    {
        // Attempt sẽ verify password và tạo token nếu thành công
        // JWTAuth::attempt() trả về token string hoặc false
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return null;
        }

        return $this->respondWithToken($token);
    }

    /**
     * Đăng xuất
     * 
     * Logic:
     * - Invalidate token hiện tại bằng cách thêm vào blacklist
     * - Token đã invalidate sẽ không thể sử dụng lại
     * - Blacklist được lưu trong cache (default: file)
     * 
     * Lưu ý: JWT là stateless, server không lưu session.
     * Invalidate hoạt động bằng cách blacklist token ID (jti claim).
     * 
     * @return void
     */
    public function logout(): void
    {
        // Invalidate token hiện tại
        // Token sẽ được thêm vào blacklist và không thể sử dụng lại
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    /**
     * Refresh token
     * 
     * Logic quan trọng:
     * - Tạo token MỚI từ token CŨ (token cũ phải còn hợp lệ)
     * - Token cũ sẽ bị invalidate sau khi refresh
     * - Thường dùng khi token sắp hết hạn
     * 
     * Refresh flow:
     * 1. Frontend check token sắp hết hạn (exp claim)
     * 2. Gọi /refresh với token cũ
     * 3. Nhận token mới, lưu lại
     * 4. Tiếp tục sử dụng token mới
     * 
     * @return array - Token info mới
     */
    public function refresh(): array
    {
        // Refresh sẽ invalidate token cũ và tạo token mới
        $new_token = JWTAuth::refresh(JWTAuth::getToken());

        return $this->respondWithToken($new_token);
    }

    /**
     * Lấy thông tin user hiện tại từ token
     * 
     * Logic:
     * - JWT middleware đã decode token và set user vào auth()
     * - Chỉ cần gọi auth()->user() để lấy thông tin
     * 
     * @return User - User model instance
     */
    public function me(): User
    {
        // JWTAuth::user() trả về User model từ token
        // Token đã được verify bởi middleware 'auth:api'
        return JWTAuth::user();
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Format token response theo chuẩn
     * 
     * Response format:
     * - access_token: JWT token string (3 phần: header.payload.signature)
     * - token_type: "bearer" (chuẩn OAuth2)
     * - expires_in: Thời gian hết hạn tính bằng giây
     * 
     * Frontend sử dụng:
     * - Lưu access_token vào localStorage/cookie
     * - Gửi kèm header: Authorization: Bearer {access_token}
     * - Check expires_in để biết khi nào cần refresh
     * 
     * @param string $token - JWT token
     * @return array
     */
    protected function respondWithToken(string $token): array
    {
        // TTL được config trong config/jwt.php (default: 60 phút)
        // Lấy từ config để tính expires_in
        $ttl = config('jwt.ttl', 60); // phút
        
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttl * 60, // convert sang giây
        ];
    }
}
