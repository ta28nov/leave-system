<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * Model đại diện cho user trong hệ thống.
 * Implement JWTSubject để hỗ trợ JWT Authentication.
 * 
 * Loại user: 0 = Admin (toàn quyền), 1 = Manager (duyệt đơn), 2 = Employee (tạo đơn)
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    // Khóa chính dạng CHAR(10), không tự tăng
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'type',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ======================== JWT Methods ========================

    /** Trả về primary key để lưu vào claim "sub" của JWT */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /** Thêm custom claims vào JWT payload (type để frontend biết role) */
    public function getJWTCustomClaims(): array
    {
        return [
            'type' => $this->type,
        ];
    }

    // ======================== Quan hệ ========================

    /** Một user có nhiều đơn xin nghỉ phép */
    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class, 'user_id', 'id');
    }
}
