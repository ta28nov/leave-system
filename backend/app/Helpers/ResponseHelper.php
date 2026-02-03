<?php

/**
 * ResponseHelper - API Response Formatter
 * 
 * Helper class để format tất cả API responses theo chuẩn thống nhất.
 * Đảm bảo response format nhất quán trong toàn hệ thống.
 * 
 * Response format (theo Section 5.3):
 * {
 *     "success": true/false,
 *     "message": "Thông báo / Message",
 *     "data": { ... } hoặc null
 * }
 * 
 * Sử dụng:
 * - Controller: return ResponseHelper::success('Message', $data, 200);
 * - Controller: return ResponseHelper::error('Error', null, 400);
 * 
 * @see AuthController
 * @see LeaveApplicationController
 */

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * Tạo success response
     * 
     * Dùng cho các request thành công (200, 201).
     * 
     * @param string $message Thông báo (song ngữ VI/EN)
     * @param mixed $data Dữ liệu trả về (object, array, hoặc null)
     * @param int $status_code HTTP status code (default: 200)
     * @return JsonResponse
     * 
     * @example
     * return ResponseHelper::success('Tạo thành công. / Created.', $user, 201);
     */
    public static function success(string $message, $data = null, int $status_code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status_code);
    }

    /**
     * Tạo error response
     * 
     * Dùng cho các request thất bại (400, 401, 403, 404, 422, 500).
     * 
     * @param string $message Thông báo lỗi (song ngữ VI/EN)
     * @param mixed $data Chi tiết lỗi (validation errors, null, etc.)
     * @param int $status_code HTTP status code (default: 400)
     * @return JsonResponse
     * 
     * @example
     * return ResponseHelper::error('Không tìm thấy. / Not found.', null, 404);
     */
    public static function error(string $message, $data = null, int $status_code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status_code);
    }
}
