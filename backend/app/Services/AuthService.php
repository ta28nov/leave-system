<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Service Layer chứa logic nghiệp vụ cho Authentication.
 * 
 * Luồng xác thực: Login → JWT Token → Gửi kèm header Authorization: Bearer {token}
 */
class AuthService
{
    /**
     * Đăng ký user mới.
     * Tạo user với type mặc định là Employee (2), tự động hash password qua $casts.
     */
    public function register(array $data): array
    {
        $data['id'] = strtoupper(Str::random(10));
        
        if (!isset($data['type'])) {
            $data['type'] = 2; // Mặc định là Employee
        }

        $user = User::create($data);
        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token);
    }

    /**
     * Đăng nhập.
     * Xác thực email/password, trả về JWT token nếu thành công.
     */
    public function login(array $credentials): ?array
    {
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return null;
        }

        return $this->respondWithToken($token);
    }

    /**
     * Đăng xuất — vô hiệu hóa token hiện tại (thêm vào blacklist).
     */
    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    /**
     * Làm mới token — tạo token mới từ token cũ, token cũ bị vô hiệu hóa.
     */
    public function refresh(): array
    {
        $new_token = JWTAuth::refresh(JWTAuth::getToken());

        return $this->respondWithToken($new_token);
    }

    /**
     * Lấy thông tin user hiện tại từ token.
     */
    public function me(): User
    {
        return JWTAuth::user();
    }

    /**
     * Định dạng response token chuẩn.
     * Frontend lưu access_token và gửi kèm header: Authorization: Bearer {token}
     */
    protected function respondWithToken(string $token): array
    {
        $ttl = config('jwt.ttl', 60);
        
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttl * 60, // Chuyển sang giây
        ];
    }
}
