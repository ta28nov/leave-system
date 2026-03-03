<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model đại diện cho đơn xin nghỉ phép.
 * 
 * Trạng thái: new → pending → approved/rejected/cancelled
 * Loại nghỉ: annual (phép năm), sick (ốm), unpaid (không lương)
 */
class LeaveApplication extends Model
{
    use SoftDeletes;

    protected $table = 'leave_applications';

    // Khóa chính dạng CHAR(10), không tự tăng
    protected $keyType = 'string';
    public $incrementing = false;

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

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /** Quan hệ: Đơn nghỉ phép thuộc về một user */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
