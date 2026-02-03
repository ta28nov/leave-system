<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaveApplicationController;

/*
|--------------------------------------------------------------------------
| Leave Application API Routes
|--------------------------------------------------------------------------
|
| Route prefix: /leave-applications
| Base Middleware: auth:api (yêu cầu đăng nhập)
|
|
| Authorization strategy:
| - Sử dụng Route Model Binding với param {leaveApplication}
| - Middleware 'can:action,leaveApplication' tự động resolve Model
| - Policy methods nhận Model instance để check authorization
|
*/

Route::middleware('auth:api')->prefix('leave-applications')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | List - viewAny policy
    |--------------------------------------------------------------------------
    | Policy: viewAny(User $user) -> return true
    | Service layer filter theo role
    */
    Route::get('/', [LeaveApplicationController::class, 'index']);
    
    /*
    |--------------------------------------------------------------------------
    | Detail - view policy
    |--------------------------------------------------------------------------
    | Middleware 'can:view,leaveApplication'
    | Route Model Binding: {leaveApplication} -> LeaveApplication instance
    */
    Route::get('/{leaveApplication}', [LeaveApplicationController::class, 'show'])
        ->middleware('can:view,leaveApplication');
    
    /*
    |--------------------------------------------------------------------------
    | Create - create policy
    |--------------------------------------------------------------------------
    | Policy: create(User $user) -> return true
    */
    Route::post('/', [LeaveApplicationController::class, 'store']);
    
    /*
    |--------------------------------------------------------------------------
    | Update - update policy
    |--------------------------------------------------------------------------
    | Middleware 'can:update,leaveApplication'
    | Policy: Owner + status='new', hoặc Admin
    */
    Route::put('/{leaveApplication}', [LeaveApplicationController::class, 'update'])
        ->middleware('can:update,leaveApplication');
    
    /*
    |--------------------------------------------------------------------------
    | Delete - delete policy
    |--------------------------------------------------------------------------
    | Middleware 'can:delete,leaveApplication'
    | Policy: Admin only
    */
    Route::delete('/{leaveApplication}', [LeaveApplicationController::class, 'destroy'])
        ->middleware('can:delete,leaveApplication');
    
    /*
    |--------------------------------------------------------------------------
    | Approve - approve policy (custom action)
    |--------------------------------------------------------------------------
    | Middleware 'can:approve,leaveApplication'
    | Policy: Manager/Admin
    */
    Route::post('/{leaveApplication}/approve', [LeaveApplicationController::class, 'approve'])
        ->middleware('can:approve,leaveApplication');
    
    /*
    |--------------------------------------------------------------------------
    | Reject - reject policy (custom action)
    |--------------------------------------------------------------------------
    | Middleware 'can:reject,leaveApplication'
    | Policy: Manager/Admin
    */
    Route::post('/{leaveApplication}/reject', [LeaveApplicationController::class, 'reject'])
        ->middleware('can:reject,leaveApplication');
    
    /*
    |--------------------------------------------------------------------------
    | Cancel - cancel policy (custom action)
    |--------------------------------------------------------------------------
    | Middleware 'can:cancel,leaveApplication'
    | Policy: Owner (chưa approved/rejected), hoặc Admin
    */
    Route::post('/{leaveApplication}/cancel', [LeaveApplicationController::class, 'cancel'])
        ->middleware('can:cancel,leaveApplication');
});
