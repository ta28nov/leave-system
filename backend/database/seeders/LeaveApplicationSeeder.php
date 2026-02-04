<?php

/**
 * LeaveApplicationSeeder
 * 
 * Seeder tạo dữ liệu mẫu cho bảng leave_applications.
 * Tạo các đơn nghỉ phép với các trạng thái khác nhau.
 * 
 * Chạy: php artisan db:seed --class=LeaveApplicationSeeder
 * 
 * @see LeaveApplication
 * @see LeaveApplicationStatus
 * @see LeaveApplicationType
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LeaveApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Tạo 10 leave applications mẫu với các trạng thái khác nhau
     */
    public function run(): void
    {
        // Lấy danh sách user IDs (employees)
        $employeeIds = DB::table('users')
            ->where('type', 2) // EMPLOYEE
            ->pluck('id')
            ->toArray();

        if (empty($employeeIds)) {
            $this->command->warn('⚠️  No employees found. Run UserSeeder first!');
            return;
        }

        $applications = [
            // Đơn PENDING - Chờ duyệt
            [
                'id' => $this->generateId(),
                'user_id' => $employeeIds[0],
                'type' => 0, // SICK_LEAVE
                'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'reason' => 'Bị cảm cúm, cần nghỉ dưỡng bệnh',
                'status' => 0, // PENDING
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $this->generateId(),
                'user_id' => $employeeIds[1] ?? $employeeIds[0],
                'type' => 1, // ANNUAL_LEAVE
                'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                'reason' => 'Nghỉ phép năm, đi du lịch cùng gia đình',
                'status' => 0, // PENDING
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Đơn APPROVED - Đã duyệt
            [
                'id' => $this->generateId(),
                'user_id' => $employeeIds[0],
                'type' => 2, // PERSONAL_LEAVE
                'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'reason' => 'Giải quyết việc cá nhân',
                'status' => 1, // APPROVED
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(8),
            ],
            [
                'id' => $this->generateId(),
                'user_id' => $employeeIds[1] ?? $employeeIds[0],
                'type' => 0, // SICK_LEAVE
                'start_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(14)->format('Y-m-d'),
                'reason' => 'Khám sức khỏe định kỳ',
                'status' => 1, // APPROVED
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(18),
            ],

            // Đơn REJECTED - Bị từ chối
            [
                'id' => $this->generateId(),
                'user_id' => $employeeIds[0],
                'type' => 1, // ANNUAL_LEAVE
                'start_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'reason' => 'Nghỉ đột xuất',
                'status' => 2, // REJECTED
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(1),
            ],

            // Thêm 5 đơn nữa để có đủ data test
            [
                'id' => $this->generateId(),
                'user_id' => $employeeIds[0],
                'type' => 3, // MATERNITY_LEAVE
                'start_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'end_date' => Carbon::now()->addMonths(4)->format('Y-m-d'),
                'reason' => 'Nghỉ thai sản',
                'status' => 0, // PENDING
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $this->generateId(),
                'user_id' => $employeeIds[1] ?? $employeeIds[0],
                'type' => 4, // UNPAID_LEAVE
                'start_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(25)->format('Y-m-d'),
                'reason' => 'Nghỉ không lương để đi công tác riêng',
                'status' => 0, // PENDING
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $this->generateId(),
                'user_id' => $employeeIds[0],
                'type' => 1, // ANNUAL_LEAVE
                'start_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(28)->format('Y-m-d'),
                'reason' => 'Nghỉ phép năm tháng trước',
                'status' => 1, // APPROVED
                'created_at' => Carbon::now()->subDays(40),
                'updated_at' => Carbon::now()->subDays(35),
            ],
            [
                'id' => $this->generateId(),
                'user_id' => $employeeIds[1] ?? $employeeIds[0],
                'type' => 0, // SICK_LEAVE
                'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
                'reason' => 'Đau răng cần đi nha khoa',
                'status' => 2, // REJECTED
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $this->generateId(),
                'user_id' => $employeeIds[0],
                'type' => 2, // PERSONAL_LEAVE
                'start_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(16)->format('Y-m-d'),
                'reason' => 'Tham dự lễ cưới người thân',
                'status' => 0, // PENDING
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert tất cả applications
        DB::table('leave_applications')->insert($applications);
        
        // Log thông báo
        $this->command->info('✅ Created 10 leave applications with various statuses');
        $this->command->info('   - Pending: 5 applications');
        $this->command->info('   - Approved: 3 applications');
        $this->command->info('   - Rejected: 2 applications');
    }

    /**
     * Generate unique 10-character ID
     */
    private function generateId(): string
    {
        return strtoupper(Str::random(10));
    }
}
