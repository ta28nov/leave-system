<?php

namespace App\Enums;

/**
 * Enum loại user trong hệ thống.
 * ADMIN (0): Toàn quyền | MANAGER (1): Duyệt đơn | EMPLOYEE (2): Tạo đơn
 */
enum UserType: int
{
    case ADMIN = 0;
    case MANAGER = 1;
    case EMPLOYEE = 2;

    public function description(): string
    {
        return match($this) {
            self::ADMIN => 'Quản trị viên',
            self::MANAGER => 'Quản lý',
            self::EMPLOYEE => 'Nhân viên',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
