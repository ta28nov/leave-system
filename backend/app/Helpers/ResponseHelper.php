<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Helper định dạng tất cả API response theo chuẩn thống nhất.Đảm bảo mọi response đều có cấu trúc giống nhau, frontend luôn nhận cùng 1 format
 * Format: { "success": true/false, "message": "...", "data": ... }
 */
class ResponseHelper
{
    /** Tạo response thành công (200, 201) */
    public static function success(string $message, $data = null, int $status_code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status_code);
    }

    /** Tạo response lỗi (400, 401, 403, 404, 422, 500) */
    public static function error(string $message, $data = null, int $status_code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status_code);
    }
}
