<?php

/**
 * User Model
 * 
 * Đại diện cho user trong hệ thống.
 * Table: users
 * 
 * Implement JWTSubject để hỗ trợ JWT Authentication.
 * JWT sử dụng user ID làm subject claim trong token.
 * 
 * User Types (theo Section 3):
 * - 0 (ADMIN): Toàn quyền - CRUD tất cả đơn, Approve/Reject
 * - 1 (MANAGER): Xem tất cả đơn, Approve/Reject
 * - 2 (EMPLOYEE): CRUD đơn của mình, Cancel
 * 
 * @see UserType
 * @see LeaveApplication
 * @see AuthService
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

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
     */
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

    /**
     * The attributes that should be hidden for serialization.
     * Ẩn password khi trả về JSON response
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Methods
    |--------------------------------------------------------------------------
    |
    | Các methods bắt buộc khi implement JWTSubject interface.
    | Được gọi tự động khi tạo JWT token cho user.
    |
    */

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     * 
     * Trả về primary key của user (id).
     * Giá trị này sẽ được lưu trong "sub" claim của JWT payload.
     * Khi decode token, JWT sẽ dùng giá trị này để tìm user.
     * 
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     * 
     * Custom claims sẽ được thêm vào JWT payload.
     * Frontend có thể decode token để lấy các thông tin này mà không cần gọi API /me.
     * 
     * Lưu ý: Không nên lưu thông tin nhạy cảm vì JWT payload có thể decode được.
     * 
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'type' => $this->type, // 0: Admin, 1: Manager, 2: Employee
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Relationship: User hasMany LeaveApplication
     * Một user có thể có nhiều đơn xin nghỉ phép
     */
    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class, 'user_id', 'id');
    }
}

