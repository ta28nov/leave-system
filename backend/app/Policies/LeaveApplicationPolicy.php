<?php

namespace App\Policies;

use App\Models\LeaveApplication;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Auth\Access\Response;

/**
 * LeaveApplicationPolicy
 * 
 * Policy class định nghĩa authorization rules cho LeaveApplication.
 * 
 * Phân quyền:
 * - Admin (type=0): Toàn quyền
 * - Manager (type=1): Xem tất cả, Approve/Reject
 * - Employee (type=2): View/Create/Update/Cancel đơn của mình
 */
class LeaveApplicationPolicy
{
    /**
     * Kiểm tra user có phải Admin không
     * Cast sang int để đảm bảo so sánh chính xác
     */
    protected function isAdmin(User $user): bool
    {
        return (int) $user->type === UserType::ADMIN->value;
    }

    /**
     * Kiểm tra user có phải Manager không
     */
    protected function isManager(User $user): bool
    {
        return (int) $user->type === UserType::MANAGER->value;
    }

    /**
     * Kiểm tra user có phải Employee không
     */
    protected function isEmployee(User $user): bool
    {
        return (int) $user->type === UserType::EMPLOYEE->value;
    }

    /**
     * Kiểm tra user có phải chủ đơn không
     */
    protected function isOwner(User $user, LeaveApplication $leave_application): bool
    {
        return $leave_application->user_id === $user->id;
    }

    /**
     * Method chạy trước tất cả policy methods khác
     * Admin có toàn quyền, bypass tất cả checks
     * 
     * @param User $user
     * @param string $ability - Tên action (view, create, update,...)
     * @return bool|null - true: cho phép, false: từ chối, null: tiếp tục check
     */
    public function before(User $user, string $ability): bool|null
    {
        if ((int) $user->type === 0) {
            return true;
        }

        return null;
    }

    /**
     * Xem danh sách đơn nghỉ phép
     * 
     * Endpoint: GET /api/leave-applications
     * Tất cả user đã đăng nhập đều được xem list
     * Service layer sẽ filter theo role
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Tạo đơn nghỉ phép mới
     * 
     * Endpoint: POST /api/leave-applications
     * Tất cả user đều có thể tạo đơn
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Xem chi tiết một đơn nghỉ phép
     * 
     * Endpoint: GET /api/leave-applications/{id}
     * Manager xem được tất cả, Employee chỉ xem đơn của mình
     */
    public function view(User $user, LeaveApplication $leave_application): bool
    {
        if ($this->isManager($user)) {
            return true;
        }

        return $this->isOwner($user, $leave_application);
    }

    /**
     * Cập nhật đơn nghỉ phép
     * 
     * Endpoint: PUT /api/leave-applications/{id}
     * Manager không được update, chỉ approve/reject
     * Employee chỉ update đơn của mình khi status = 'new'
     */
    public function update(User $user, LeaveApplication $leave_application): bool
    {
        if ($this->isManager($user)) {
            return false;
        }

        if (!$this->isOwner($user, $leave_application)) {
            return false;
        }

        return $leave_application->status === 'new';
    }

    /**
     * Xóa đơn nghỉ phép
     * 
     * Endpoint: DELETE /api/leave-applications/{id}
     * Chỉ Admin được xóa (đã xử lý trong before())
     */
    public function delete(User $user, LeaveApplication $leave_application): bool
    {
        return false;
    }

    /**
     * Duyệt đơn nghỉ phép
     * 
     * Endpoint: POST /api/leave-applications/{id}/approve
     * Manager và Admin có thể approve
     */
    public function approve(User $user, LeaveApplication $leave_application): bool
    {
        return $this->isManager($user);
    }

    /**
     * Từ chối đơn nghỉ phép
     * 
     * Endpoint: POST /api/leave-applications/{id}/reject
     * Manager và Admin có thể reject
     */
    public function reject(User $user, LeaveApplication $leave_application): bool
    {
        return $this->isManager($user);
    }

    /**
     * Hủy đơn nghỉ phép
     * 
     * Endpoint: POST /api/leave-applications/{id}/cancel
     * Employee chỉ hủy đơn của mình khi chưa approved/rejected
     * Manager không được cancel
     */
    public function cancel(User $user, LeaveApplication $leave_application): bool
    {
        if ($this->isManager($user)) {
            return false;
        }

        if (!$this->isOwner($user, $leave_application)) {
            return false;
        }

        return !in_array($leave_application->status, ['approved', 'rejected']);
    }

    /**
     * Khôi phục đơn đã xóa mềm
     * Chỉ Admin (xử lý trong before())
     */
    public function restore(User $user, LeaveApplication $leave_application): bool
    {
        return false;
    }

    /**
     * Xóa vĩnh viễn đơn
     * Chỉ Admin (xử lý trong before())
     */
    public function forceDelete(User $user, LeaveApplication $leave_application): bool
    {
        return false;
    }
}
