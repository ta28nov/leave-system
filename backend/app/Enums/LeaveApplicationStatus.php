<?php

/**
 * LeaveApplicationStatus Enum
 * 
 * Định nghĩa các trạng thái của đơn nghỉ phép.
 * Dùng string values để dễ đọc trong database và response.
 * 
 * Workflow:
 * new → pending → approved/rejected
 *              → cancelled (từ new/pending)
 * 
 * Các trạng thái:
 * - NEW: Đơn mới tạo, chưa gửi
 * - PENDING: Đã gửi, đang chờ duyệt
 * - APPROVED: Đã được duyệt bởi Manager/Admin
 * - REJECTED: Bị từ chối bởi Manager/Admin
 * - CANCELLED: Đã hủy bởi Owner/Admin
 * 
 * @see LeaveApplication
 * @see LeaveApplicationService
 */

namespace App\Enums;

enum LeaveApplicationStatus: string
{
    case NEW = 'new';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    /**
     * Get description for leave status
     * 
     * Trả về mô tả tiếng Việt cho từng trạng thái.
     */
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

    /**
     * Get all values as array
     * 
     * Trả về mảng các giá trị string.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
