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
 * LeaveApplicationService
 * 
 * Service Layer chứa toàn bộ logic nghiệp vụ cho Leave Applications.
 * Bao gồm cả Authorization logic theo Role.
 * 
 * Phân quyền (theo Section 3):
 * - Admin (type=0): Toàn quyền - CRUD tất cả đơn, Approve/Reject
 * - Manager (type=1): Xem tất cả đơn, Approve/Reject
 * - Employee (type=2): View/Create/Update/Cancel đơn của mình
 * 
 * Logic quan trọng:
 * - List: Employee chỉ thấy đơn của mình, Manager/Admin thấy tất cả
 * - Detail: Employee chỉ xem đơn của mình, Manager/Admin xem tất cả
 * - Update: Chỉ owner hoặc Admin được sửa, và đơn phải status = new
 * - Delete: Chỉ Admin được xóa
 * - Approve/Reject: Chỉ Manager/Admin
 * - Cancel: Chỉ owner
 */
class LeaveApplicationService
{
    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Lấy user hiện tại đang đăng nhập
     */
    protected function getCurrentUser(): User
    {
        return Auth::user();
    }

    /**
     * Check xem user co phai Admin khong
     * Cast sang int de dam bao so sanh chinh xac
     */
    protected function isAdmin(): bool
    {
        return (int) $this->getCurrentUser()->type === UserType::ADMIN->value;
    }

    /**
     * Check xem user co phai Manager khong
     * Cast sang int de dam bao so sanh chinh xac
     */
    protected function isManager(): bool
    {
        return (int) $this->getCurrentUser()->type === UserType::MANAGER->value;
    }

    /**
     * Check xem user co phai Employee khong
     * Cast sang int de dam bao so sanh chinh xac
     */
    protected function isEmployee(): bool
    {
        return (int) $this->getCurrentUser()->type === UserType::EMPLOYEE->value;
    }

    /**
     * Check xem user có quyền quản lý (Admin hoặc Manager)
     */
    protected function isManagerOrAdmin(): bool
    {
        return (int) $this->isAdmin() || $this->isManager();
    }

    /**
     * Check ownership - user có phải là chủ đơn không
     */
    protected function isOwner(LeaveApplication $leave_application): bool
    {
        return $leave_application->user_id === Auth::id();
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD Methods với Authorization
    |--------------------------------------------------------------------------
    */

    /**
     * Get list with pagination, filters, and sorting
     * 
     * Authorization:
     * - Employee: CHỈ thấy đơn của mình
     * - Manager/Admin: Thấy TẤT CẢ đơn
     * 
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList(array $filters)
    {
        $query = LeaveApplication::query()->with('user');

        /*
        |--------------------------------------------------------------------------
        | Authorization Filter
        |--------------------------------------------------------------------------
        |
        | Employee chỉ được xem đơn của mình.
        | Tự động filter theo user_id = current user id.
        |
        */
        if ($this->isEmployee()) {
            $query->where('user_id', Auth::id());
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by user_id (chỉ Manager/Admin dùng được filter này)
        // Employee không thể xem đơn của người khác
        if (isset($filters['user_id']) && $this->isManagerOrAdmin()) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by month and year
        if (isset($filters['month']) && isset($filters['year'])) {
            $query->whereYear('start_date', $filters['year'])
                  ->whereMonth('start_date', $filters['month']);
        }

        // Sort by date (mới nhất trước)
        $query->orderBy('start_date', 'desc');

        // Pagination: 10 per page
        return $query->paginate(10);
    }

    /**
     * Get detail with relationships
     * 
     * Authorization: Handled by Policy middleware
     * 
     * @param string $id
     * @return LeaveApplication
     */
    public function getDetail(string $id)
    {
        $leave_application = LeaveApplication::with('user')->findOrFail($id);

        // Policy middleware đã check quyền view
        return $leave_application;
    }

    /**
     * Create new leave application
     * 
     * Authorization:
     * - Tất cả user đã đăng nhập đều có thể tạo đơn
     * - Đơn được tạo sẽ thuộc về user hiện tại
     * 
     * Logic:
     * 1. Generate unique ID (char(10))
     * 2. Calculate total days
     * 3. Set user_id = current user
     * 4. Set created_by = current user
     * 
     * @param array $data
     * @return LeaveApplication
     */
    public function create(array $data)
    {
        // Generate unique ID dạng char(10)
        $data['id'] = strtoupper(Str::random(10));
        
        // Calculate total days (inclusive)
        $start_date = Carbon::parse($data['start_date']);
        $end_date = Carbon::parse($data['end_date']);
        $data['total_days'] = $end_date->diffInDays($start_date) + 1;

        // Set user_id = người tạo đơn
        $data['user_id'] = Auth::id();
        
        // Audit log: ai tạo
        $data['created_by'] = Auth::id();

        return LeaveApplication::create($data);
    }

    /**
     * Update leave application
     * 
     * Authorization: Handled by Policy middleware
     * 
     * Business Rule:
     * - Chỉ được sửa khi đơn có status = 'new'
     * - Admin có thể bypass status check (handled by Policy)
     * 
     * @param string $id
     * @param array $data
     * @return LeaveApplication
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function update(string $id, array $data)
    {
        $leave_application = LeaveApplication::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Business Rule: Status Check
        |--------------------------------------------------------------------------
        |
        | Chỉ cho phép sửa đơn có status = 'new'.
        | Đơn đã pending/approved/rejected/cancelled không sửa được.
        | Note: Admin có thể bypass qua Policy, nhưng vẫn phải tuân thủ rule này
        |
        */
        if (!$this->isAdmin() && $leave_application->status !== 'new') {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                'Chỉ có thể sửa đơn ở trạng thái "new". / Can only update applications with status "new".'
            );
        }

        // Recalculate total days if any date changed
        if (isset($data['start_date']) || isset($data['end_date'])) {
            $start_date = Carbon::parse($data['start_date'] ?? $leave_application->start_date);
            $end_date = Carbon::parse($data['end_date'] ?? $leave_application->end_date);
            $data['total_days'] = $end_date->diffInDays($start_date) + 1;
        }

        // Audit log: ai sửa
        $data['updated_by'] = Auth::id();

        $leave_application->update($data);

        return $leave_application->fresh();
    }

    /**
     * Soft delete leave application
     * 
     * Authorization: Handled by Policy middleware (Admin only)
     * 
     * @param string $id
     * @return bool
     */
    public function delete(string $id)
    {
        $leave_application = LeaveApplication::findOrFail($id);

        // Policy middleware đã check quyền delete (Admin only)
        
        // Audit log: ai xóa
        $leave_application->deleted_by = Auth::id();
        $leave_application->save();

        // Soft delete
        return $leave_application->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Actions với Authorization
    |--------------------------------------------------------------------------
    */

    /**
     * Approve leave application
     * 
     * Authorization:
     * - Manager/Admin: Được approve
     * - Employee: KHÔNG được (403)
     * 
     * Business Rule:
     * - Chỉ approve được đơn ở trạng thái 'new' hoặc 'pending'
     * 
     * @param string $id
     * @return LeaveApplication
     * @throws AccessDeniedHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function approve(string $id)
    {
        $leave_application = LeaveApplication::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | State Transition Validation
        |--------------------------------------------------------------------------
        |
        | Chỉ cho phép approve đơn ở trạng thái 'new' hoặc 'pending'.
        | Workflow: new → pending → approved
        |
        */
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
     * Reject leave application
     * 
     * Authorization:
     * - Manager/Admin: Được reject
     * - Employee: KHÔNG được (403)
     * 
     * Business Rule:
     * - Chỉ reject được đơn ở trạng thái 'new' hoặc 'pending'
     * 
     * @param string $id
     * @param string $reason - Lý do từ chối (bắt buộc)
     * @return LeaveApplication
     * @throws AccessDeniedHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function reject(string $id, string $reason)
    {
        $leave_application = LeaveApplication::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | State Transition Validation
        |--------------------------------------------------------------------------
        |
        | Chỉ cho phép reject đơn ở trạng thái 'new' hoặc 'pending'.
        | Workflow: new → pending → rejected
        |
        */
        if (!in_array($leave_application->status, ['new', 'pending'])) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                'Chỉ có thể từ chối đơn ở trạng thái new hoặc pending. / Can only reject applications with status new or pending.'
            );
        }

        $leave_application->status = 'rejected';
        $leave_application->reason = $reason; // Lưu lý do từ chối
        $leave_application->updated_by = Auth::id();
        $leave_application->save();

        return $leave_application;
    }

    /**
     * Cancel leave application
     * 
     * Authorization: Handled by Policy middleware
     * 
     * Business Rule:
     * - Không cancel được đơn đã approved/rejected
     * 
     * @param string $id
     * @return LeaveApplication
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function cancel(string $id)
    {
        $leave_application = LeaveApplication::findOrFail($id);

        // Policy middleware đã check quyền cancel (Owner or Admin)

        /*
        |--------------------------------------------------------------------------
        | Business Rule: Status Check
        |--------------------------------------------------------------------------
        |
        | Không cho phép hủy đơn đã được duyệt hoặc từ chối.
        |
        */
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

