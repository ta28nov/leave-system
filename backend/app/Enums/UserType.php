<?php

/**
 * UserType Enum
 * 
 * Định nghĩa các loại user trong hệ thống (theo Section 3).
 * Dùng integer values để lưu trong database, dễ so sánh.
 * 
 * Phân quyền:
 * - ADMIN (0): Toàn quyền - CRUD tất cả, Approve/Reject
 * - MANAGER (1): Xem tất cả đơn, Approve/Reject
 * - EMPLOYEE (2): CRUD đơn của mình, Cancel
 * 
 * Sử dụng:
 * - Model: 'type' => UserType::ADMIN->value
 * - Policy: $user->type === UserType::ADMIN->value
 * 
 * @see User
 * @see LeaveApplicationPolicy
 */

namespace App\Enums;

enum UserType: int
{
    case ADMIN = 0;
    case MANAGER = 1;
    case EMPLOYEE = 2;

    /**
     * Get description for user type
     * 
     * Trả về mô tả tiếng Việt cho từng loại user.
     */
    public function description(): string
    {
        return match($this) {
            self::ADMIN => 'Quản trị viên - Toàn quyền',
            self::MANAGER => 'Quản lý - Duyệt đơn',
            self::EMPLOYEE => 'Nhân viên - Tạo đơn',
        };
    }

    /**
     * Get all values as array
     * 
     * Trả về mảng các giá trị integer: [0, 1, 2]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
