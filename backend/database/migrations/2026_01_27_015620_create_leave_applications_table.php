<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Migration tạo bảng leave_applications */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            // Khóa chính dạng CHAR(10)
            $table->char('id', 10)->primary();
            
            // Khóa ngoại liên kết bảng users
            $table->char('user_id', 10);
            
            $table->date('start_date');
            $table->date('end_date');
            $table->float('total_days');
            $table->text('reason')->nullable();
            
            // Loại nghỉ: annual, sick, unpaid
            $table->string('type', 50);
            
            // Trạng thái: new, pending, approved, rejected, cancelled
            $table->string('status', 50)->default('new');
            
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable(); // Xóa mềm
            
            // Trường audit: ai tạo/sửa/xóa
            $table->char('created_by', 10)->nullable();
            $table->char('updated_by', 10)->nullable();
            $table->char('deleted_by', 10)->nullable();
            
            // Khóa ngoại
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            
            // Chỉ mục tối ưu truy vấn
            $table->index('user_id');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('deleted_at');
            
            // Chỉ mục kết hợp cho kiểm tra trùng ngày (overlap check)
            $table->index(['user_id', 'start_date', 'end_date'], 'idx_user_date_range');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
