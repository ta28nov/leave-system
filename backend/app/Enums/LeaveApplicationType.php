<?php

/**
 * LeaveApplicationType Enum
 * 
 * Định nghĩa các loại nghỉ phép trong hệ thống.
 * Dùng string values để dễ đọc trong database và response.
 * 
 * Các loại:
 * - ANNUAL: Nghỉ phép năm (có lương)
 * - SICK: Nghỉ ốm (có giấy bác sĩ)
 * - UNPAID: Nghỉ không lương
 * 
 * Sử dụng trong validation:
 * - 'in:' . implode(',', LeaveApplicationType::values())
 * 
 * @see LeaveApplication
 * @see CreateLeaveApplicationRequest
 */

namespace App\Enums;

enum LeaveApplicationType: string
{
    case ANNUAL = 'annual';
    case SICK = 'sick';
    case UNPAID = 'unpaid';

    /**
     * Get description for leave type
     * 
     * Trả về mô tả tiếng Việt cho từng loại nghỉ.
     */
    public function description(): string
    {
        return match ($this) {
            self::ANNUAL => 'Nghỉ phép năm',
            self::SICK => 'Nghỉ ốm',
            self::UNPAID => 'Nghỉ không lương',
        };
    }

    /**
     * Get all values as array
     * 
     * Trả về mảng các giá trị string: ['annual', 'sick', 'unpaid']
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
