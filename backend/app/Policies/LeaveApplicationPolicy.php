<?php

namespace App\Policies;

use App\Models\LeaveApplication;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Auth\Access\Response;

/**
 * Policy định nghĩa quyền truy cập cho LeaveApplication.
 * 
 * Admin (type=0): Toàn quyền (bypass qua before())
 * Manager (type=1): Xem tất cả, Approve/Reject
 * Employee (type=2): CRUD đơn của mình, Cancel
 */
class LeaveApplicationPolicy
{
    protected function isAdmin(User $user): bool
    {
        return (int) $user->type === UserType::ADMIN->value;
    }

    protected function isManager(User $user): bool
    {
        return (int) $user->type === UserType::MANAGER->value;
    }

    protected function isEmployee(User $user): bool
    {
        return (int) $user->type === UserType::EMPLOYEE->value;
    }

    protected function isOwner(User $user, LeaveApplication $leave_application): bool
    {
        return $leave_application->user_id === $user->id;
    }

    /** Admin có toàn quyền, bypass tất cả policy methods khác */
    public function before(User $user, string $ability): bool|null
    {
        if ((int) $user->type === 0) {
            return true;
        }

        return null;
    }

    /** Xem danh sách — tất cả user đều được (Service sẽ filter theo role) */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Tạo đơn — tất cả user đều được */
    public function create(User $user): bool
    {
        return true;
    }

    /** Xem chi tiết — Manager xem tất cả, Employee chỉ xem đơn mình */
    public function view(User $user, LeaveApplication $leave_application): bool
    {
        if ($this->isManager($user)) {
            return true;
        }

        return $this->isOwner($user, $leave_application);
    }

    /** Cập nhật — chỉ chủ đơn + status='new'. Manager không được update */
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

    /** Xóa — chỉ Admin (đã xử lý trong before()) */
    public function delete(User $user, LeaveApplication $leave_application): bool
    {
        return false;
    }

    /** Duyệt đơn — Manager và Admin */
    public function approve(User $user, LeaveApplication $leave_application): bool
    {
        return $this->isManager($user);
    }

    /** Từ chối đơn — Manager và Admin */
    public function reject(User $user, LeaveApplication $leave_application): bool
    {
        return $this->isManager($user);
    }

    /** Hủy đơn — chỉ chủ đơn + chưa approved/rejected. Manager không được cancel */
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

    /** Khôi phục đơn đã xóa mềm — chỉ Admin (qua before()) */
    public function restore(User $user, LeaveApplication $leave_application): bool
    {
        return false;
    }

    /** Xóa vĩnh viễn — chỉ Admin (qua before()) */
    public function forceDelete(User $user, LeaveApplication $leave_application): bool
    {
        return false;
    }
}
