<?php

namespace App\Enums;

/**
 * Enum loại nghỉ phép.
 * annual (phép năm) | sick (ốm) | unpaid (không lương)
 */
enum LeaveApplicationType: string
{
    case ANNUAL = 'annual';
    case SICK = 'sick';
    case UNPAID = 'unpaid';

    public function description(): string
    {
        return match ($this) {
            self::ANNUAL => 'Nghỉ phép năm',
            self::SICK => 'Nghỉ ốm',
            self::UNPAID => 'Nghỉ không lương',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
