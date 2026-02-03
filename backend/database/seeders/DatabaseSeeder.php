<?php

/**
 * DatabaseSeeder
 * 
 * Main seeder gọi tất cả các seeders khác.
 * Chạy: php artisan db:seed
 * 
 * Các seeders được gọi:
 * - UserSeeder: Tạo 5 users mẫu
 * 
 * @see UserSeeder
 */

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * 
     * Gọi các seeders theo thứ tự phụ thuộc:
     * 1. UserSeeder (users table) - phải chạy trước
     * 2. [Thêm seeders khác ở đây nếu cần]
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
    }
}
