<?php

namespace App\Http\Controllers;

use App\Services\LeaveApplicationService;
use App\Models\LeaveApplication;
use App\Http\Requests\CreateLeaveApplicationRequest;
use App\Http\Requests\UpdateLeaveApplicationRequest;
use App\Http\Requests\RejectLeaveRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

/**
 * Controller cho Leave Application APIs.
 * Controller chỉ nhận Request, gọi Service xử lý, và trả về Response.
 */
class LeaveApplicationController extends Controller
{
    protected $leave_application_service;

    public function __construct(LeaveApplicationService $leave_application_service)
    {
        $this->leave_application_service = $leave_application_service;
    }

    /** Lấy danh sách đơn nghỉ phép có phân trang và filters */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'user_id', 'month', 'year']);
        $data = $this->leave_application_service->getList($filters);

        return ResponseHelper::success('Lấy danh sách thành công. / List retrieved successfully.', $data);
    }

    /** Xem chi tiết một đơn nghỉ phép */
    public function show(LeaveApplication $leaveApplication)
    {
        $data = $this->leave_application_service->getDetail($leaveApplication->id);

        return ResponseHelper::success('Lấy chi tiết thành công. / Detail retrieved successfully.', $data);
    }

    /** Tạo đơn nghỉ phép mới */
    public function store(CreateLeaveApplicationRequest $request)
    {
        $data = $this->leave_application_service->create($request->validated());

        return ResponseHelper::success('Tạo đơn nghỉ phép thành công. / Leave application created successfully.', $data, 201);
    }

    /** Cập nhật đơn nghỉ phép */
    public function update(UpdateLeaveApplicationRequest $request, LeaveApplication $leaveApplication)
    {
        $data = $this->leave_application_service->update($leaveApplication->id, $request->validated());

        return ResponseHelper::success('Cập nhật đơn nghỉ phép thành công. / Leave application updated successfully.', $data);
    }

    /** Xóa đơn nghỉ phép (soft delete) */
    public function destroy(LeaveApplication $leaveApplication)
    {
        $this->leave_application_service->delete($leaveApplication->id);

        return ResponseHelper::success('Xóa đơn nghỉ phép thành công. / Leave application deleted successfully.', null);
    }

    /** Duyệt đơn nghỉ phép */
    public function approve(LeaveApplication $leaveApplication)
    {
        $data = $this->leave_application_service->approve($leaveApplication->id);

        return ResponseHelper::success('Duyệt đơn nghỉ phép thành công. / Leave application approved successfully.', $data);
    }

    /** Từ chối đơn nghỉ phép */
    public function reject(RejectLeaveRequest $request, LeaveApplication $leaveApplication)
    {
        $reason = $request->validated()['reason'];
        $data = $this->leave_application_service->reject($leaveApplication->id, $reason);

        return ResponseHelper::success('Từ chối đơn nghỉ phép thành công. / Leave application rejected successfully.', $data);
    }

    /** Hủy đơn nghỉ phép */
    public function cancel(LeaveApplication $leaveApplication)
    {
        $data = $this->leave_application_service->cancel($leaveApplication->id);

        return ResponseHelper::success('Hủy đơn nghỉ phép thành công. / Leave application cancelled successfully.', $data);
    }
}
