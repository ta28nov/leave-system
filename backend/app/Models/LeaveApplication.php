<?php

/**
 * LeaveApplication Model
 * 
 * Đại diện cho một đơn xin nghỉ phép trong hệ thống.
 * Table: leave_applications
 * 
 * Trạng thái (status):
 * - new: Đơn mới tạo, chưa gửi
 * - pending: Đã gửi, chờ duyệt
 * - approved: Đã được duyệt
 * - rejected: Bị từ chối
 * - cancelled: Đã hủy
 * 
 * Loại nghỉ phép (type):
 * - annual: Nghỉ phép năm
 * - sick: Nghỉ ốm
 * - unpaid: Nghỉ không lương
 * 
 * @see User
 * @see LeaveApplicationService
 * @see LeaveApplicationPolicy
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApplication extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'leave_applications';

    /**
     * The primary key type.
     * Sử dụng CHAR(10) thay vì BigInt auto-increment
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     * Primary key là random string, không auto-increment
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     * 
     * Các field được phép mass assign khi create/update.
     * Audit fields: created_by, updated_by, deleted_by
     */
    protected $fillable = [
        'id',
        'user_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'type',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     * 
     * Tự động convert types khi get/set.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Relationship: LeaveApplication belongsTo User
     * 
     * Một đơn nghỉ phép thuộc về một user.
     * Eager loading: LeaveApplication::with('user')->get()
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

