<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\UserType;

/**
 * Seeder tạo dữ liệu mẫu cho bảng users.
 * Tạo 5 users: 1 Admin, 2 Managers, 2 Employees.
 * Mật khẩu chung: password123
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Admin
            [
                'id' => $this->generateId(),
                'name' => 'Nguyễn Văn Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'type' => UserType::ADMIN->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Manager 1
            [
                'id' => $this->generateId(),
                'name' => 'Trần Thị Manager 1',
                'email' => 'manager1@example.com',
                'password' => Hash::make('password123'),
                'type' => UserType::MANAGER->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Manager 2
            [
                'id' => $this->generateId(),
                'name' => 'Lê Văn Manager 2',
                'email' => 'manager2@example.com',
                'password' => Hash::make('password123'),
                'type' => UserType::MANAGER->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Employee 1
            [
                'id' => $this->generateId(),
                'name' => 'Phạm Thị Employee 1',
                'email' => 'employee1@example.com',
                'password' => Hash::make('password123'),
                'type' => UserType::EMPLOYEE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Employee 2
            [
                'id' => $this->generateId(),
                'name' => 'Hoàng Văn Employee 2',
                'email' => 'employee2@example.com',
                'password' => Hash::make('password123'),
                'type' => UserType::EMPLOYEE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
        $this->command->info(' Đã tạo 5 users: 1 Admin, 2 Managers, 2 Employees');
    }

    private function generateId(): string
    {
        return strtoupper(Str::random(10));
    }
}
