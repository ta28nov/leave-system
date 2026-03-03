<?php

namespace App\Enums;

/**
 * Enum trạng thái đơn nghỉ phép.
 * Luồng: new → pending → approved/rejected | new/pending → cancelled
 */
enum LeaveApplicationStatus: string
{
    case NEW = 'new';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function description(): string
    {
        return match($this) {
            self::NEW => 'Mới tạo',
            self::PENDING => 'Chờ duyệt',
            self::APPROVED => 'Đã duyệt',
            self::REJECTED => 'Từ chối',
            self::CANCELLED => 'Đã hủy',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
