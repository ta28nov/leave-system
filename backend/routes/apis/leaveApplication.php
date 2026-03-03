<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaveApplicationController;

/*
 * Route Leave Application: /api/leave-applications
 * Tất cả route yêu cầu đăng nhập (auth:api).
 * Phân quyền qua Policy middleware (can:action,leaveApplication).
 */

Route::middleware('auth:api')->prefix('leave-applications')->group(function () {
    
    // Danh sách (Service tự filter theo role)
    Route::get('/', [LeaveApplicationController::class, 'index']);
    
    // Chi tiết
    Route::get('/{leaveApplication}', [LeaveApplicationController::class, 'show'])
        ->middleware('can:view,leaveApplication');
    
    // Tạo mới
    Route::post('/', [LeaveApplicationController::class, 'store']);
    
    // Cập nhật (chỉ chủ đơn + status='new', hoặc Admin)
    Route::put('/{leaveApplication}', [LeaveApplicationController::class, 'update'])
        ->middleware('can:update,leaveApplication');
    
    // Xóa mềm (chỉ Admin)
    Route::delete('/{leaveApplication}', [LeaveApplicationController::class, 'destroy'])
        ->middleware('can:delete,leaveApplication');
    
    // Duyệt đơn (Manager/Admin)
    Route::post('/{leaveApplication}/approve', [LeaveApplicationController::class, 'approve'])
        ->middleware('can:approve,leaveApplication');
    
    // Từ chối đơn (Manager/Admin)
    Route::post('/{leaveApplication}/reject', [LeaveApplicationController::class, 'reject'])
        ->middleware('can:reject,leaveApplication');
    
    // Hủy đơn (chủ đơn + chưa approved/rejected, hoặc Admin)
    Route::post('/{leaveApplication}/cancel', [LeaveApplicationController::class, 'cancel'])
        ->middleware('can:cancel,leaveApplication');
});
