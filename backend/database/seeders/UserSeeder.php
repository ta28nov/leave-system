<?php

/**
 * UserSeeder
 * 
 * Seeder tạo dữ liệu mẫu cho bảng users.
 * Tạo 5 users với các role khác nhau để test phân quyền.
 * 
 * Tài khoản mẫu:
 * - admin@example.com (Admin)
 * - manager1@example.com, manager2@example.com (Managers)
 * - employee1@example.com, employee2@example.com (Employees)
 * 
 * Password chung: password123
 * 
 * Chạy: php artisan db:seed --class=UserSeeder
 * 
 * @see User
 * @see UserType
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\UserType;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Tạo 5 users: 1 Admin, 2 Managers, 2 Employees
     * Sử dụng DB::table thay vì Model để tránh issues với observers
     */
    public function run(): void
    {
        $users = [
            // 1 Admin - Toàn quyền trong hệ thống
            [
                'id' => $this->generateId(),
                'name' => 'Nguyễn Văn Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'type' => UserType::ADMIN->value, // 0
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // 2 Managers - Duyệt/Từ chối đơn
            [
                'id' => $this->generateId(),
                'name' => 'Trần Thị Manager 1',
                'email' => 'manager1@example.com',
                'password' => Hash::make('password123'),
                'type' => UserType::MANAGER->value, // 1
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $this->generateId(),
                'name' => 'Lê Văn Manager 2',
                'email' => 'manager2@example.com',
                'password' => Hash::make('password123'),
                'type' => UserType::MANAGER->value, // 1
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // 2 Employees - Tạo/Sửa/Hủy đơn của mình
            [
                'id' => $this->generateId(),
                'name' => 'Phạm Thị Employee 1',
                'email' => 'employee1@example.com',
                'password' => Hash::make('password123'),
                'type' => UserType::EMPLOYEE->value, // 2
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $this->generateId(),
                'name' => 'Hoàng Văn Employee 2',
                'email' => 'employee2@example.com',
                'password' => Hash::make('password123'),
                'type' => UserType::EMPLOYEE->value, // 2
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert tất cả users
        DB::table('users')->insert($users);
        
        // Log thông báo
        $this->command->info('✅ Created 5 users: 1 Admin, 2 Managers, 2 Employees');
    }

    /**
     * Generate unique 10-character ID
     * 
     * Format: UPPERCASE random string
     * Ví dụ: "A1B2C3D4E5"
     */
    private function generateId(): string
    {
        return strtoupper(Str::random(10));
    }
}
