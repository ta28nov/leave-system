<?php

namespace App\Services;

use App\Models\LeaveApplication;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Service Layer chứa toàn bộ logic nghiệp vụ cho Leave Applications.
 * 
 * Phân quyền:
 * - Admin (type=0): Toàn quyền
 * - Manager (type=1): Xem tất cả, Approve/Reject
 * - Employee (type=2): CRUD đơn của mình, Cancel
 */
class LeaveApplicationService
{
    // ======================== Helper Methods ========================

    protected function getCurrentUser(): User
    {
        return Auth::user();
    }

    protected function isAdmin(): bool
    {
        return (int) $this->getCurrentUser()->type === UserType::ADMIN->value;
    }

    protected function isManager(): bool
    {
        return (int) $this->getCurrentUser()->type === UserType::MANAGER->value;
    }

    protected function isEmployee(): bool
    {
        return (int) $this->getCurrentUser()->type === UserType::EMPLOYEE->value;
    }

    protected function isManagerOrAdmin(): bool
    {
        return (int) $this->isAdmin() || $this->isManager();
    }

    protected function isOwner(LeaveApplication $leave_application): bool
    {
        return $leave_application->user_id === Auth::id();
    }

    // ======================== CRUD Methods ========================

    /**
     * Lấy danh sách đơn nghỉ phép có phân trang và lọc.
     * Employee chỉ thấy đơn của mình, Manager/Admin thấy tất cả.
     */
    public function getList(array $filters)
    {
        $query = LeaveApplication::query()->with('user');

        // Employee chỉ được xem đơn của mình
        if ($this->isEmployee()) {
            $query->where('user_id', Auth::id());
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Chỉ Manager/Admin mới dùng được filter user_id
        if (isset($filters['user_id']) && $this->isManagerOrAdmin()) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['month']) && isset($filters['year'])) {
            $query->whereYear('start_date', $filters['year'])
                  ->whereMonth('start_date', $filters['month']);
        }

        $query->orderBy('start_date', 'desc');

        return $query->paginate(10);
    }

    /**
     * Xem chi tiết đơn nghỉ phép (quyền được kiểm tra bởi Policy middleware).
     */
    public function getDetail(string $id)
    {
        return LeaveApplication::with('user')->findOrFail($id);
    }

    /**
     * Tạo đơn nghỉ phép mới.
     * Tự động gán user_id = người tạo, tính total_days theo ngày lịch.
     */
    public function create(array $data)
    {
        $data['id'] = strtoupper(Str::random(10));
        
        // Tính tổng ngày nghỉ (bao gồm cả ngày đầu và ngày cuối)
        $start_date = Carbon::parse($data['start_date']);
        $end_date = Carbon::parse($data['end_date']);
        $data['total_days'] = $start_date->diffInDays($end_date) + 1;

        $data['user_id'] = Auth::id();
        $data['created_by'] = Auth::id();

        return LeaveApplication::create($data);
    }

    /**
     * Cập nhật đơn nghỉ phép.
     * Chỉ được sửa khi đơn có status = 'new' (Admin có thể bypass).
     */
    public function update(string $id, array $data)
    {
        $leave_application = LeaveApplication::findOrFail($id);

        // Kiểm tra trạng thái: chỉ đơn 'new' mới được sửa (trừ Admin)
        if (!$this->isAdmin() && $leave_application->status !== 'new') {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                'Chỉ có thể sửa đơn ở trạng thái "new". / Can only update applications with status "new".'
            );
        }

        // Tính lại total_days nếu ngày thay đổi
        if (isset($data['start_date']) || isset($data['end_date'])) {
            $start_date = Carbon::parse($data['start_date'] ?? $leave_application->start_date);
            $end_date = Carbon::parse($data['end_date'] ?? $leave_application->end_date);
            $data['total_days'] = $start_date->diffInDays($end_date) + 1;
        }

        $data['updated_by'] = Auth::id();
        $leave_application->update($data);

        return $leave_application->fresh();
    }

    /**
     * Xóa mềm đơn nghỉ phép (chỉ Admin, kiểm tra bởi Policy).
     */
    public function delete(string $id)
    {
        $leave_application = LeaveApplication::findOrFail($id);

        $leave_application->deleted_by = Auth::id();
        $leave_application->save();

        return $leave_application->delete();
    }

    // ======================== Hành động đặc biệt ========================

    /**
     * Duyệt đơn nghỉ phép.
     * Chỉ duyệt được đơn ở trạng thái 'new' hoặc 'pending'.
     */
    public function approve(string $id)
    {
        $leave_application = LeaveApplication::findOrFail($id);

        if (!in_array($leave_application->status, ['new', 'pending'])) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                'Chỉ có thể duyệt đơn ở trạng thái new hoặc pending. / Can only approve applications with status new or pending.'
            );
        }

        $leave_application->status = 'approved';
        $leave_application->updated_by = Auth::id();
        $leave_application->save();

        return $leave_application;
    }

    /**
     * Từ chối đơn nghỉ phép (bắt buộc có lý do).
     * Chỉ từ chối được đơn ở trạng thái 'new' hoặc 'pending'.
     */
    public function reject(string $id, string $reason)
    {
        $leave_application = LeaveApplication::findOrFail($id);

        if (!in_array($leave_application->status, ['new', 'pending'])) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                'Chỉ có thể từ chối đơn ở trạng thái new hoặc pending. / Can only reject applications with status new or pending.'
            );
        }

        $leave_application->status = 'rejected';
        $leave_application->reason = $reason;
        $leave_application->updated_by = Auth::id();
        $leave_application->save();

        return $leave_application;
    }

    /**
     * Hủy đơn nghỉ phép.
     * Không thể hủy đơn đã approved hoặc rejected.
     */
    public function cancel(string $id)
    {
        $leave_application = LeaveApplication::findOrFail($id);

        if (in_array($leave_application->status, ['approved', 'rejected'])) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                'Không thể hủy đơn đã được duyệt hoặc từ chối. / Cannot cancel approved or rejected applications.'
            );
        }

        $leave_application->status = 'cancelled';
        $leave_application->updated_by = Auth::id();
        $leave_application->save();

        return $leave_application;
    }
}
