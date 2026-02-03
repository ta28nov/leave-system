<?php

/**
 * CreateLeaveApplicationsTable Migration
 * 
 * Tạo bảng leave_applications trong database.
 * Bảng này lưu thông tin đơn nghỉ phép và hỗ trợ soft delete.
 * 
 * Columns:
 * - id: CHAR(10) primary key
 * - user_id: FK to users table
 * - start_date, end_date, total_days: Thông tin ngày nghỉ
 * - type: annual, sick, unpaid
 * - status: new, pending, approved, rejected, cancelled
 * - Audit fields: created_by, updated_by, deleted_by
 * 
 * Indexes:
 * - Composite index cho overlap checking
 * 
 * @see LeaveApplication
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            // Primary Key: char(10)
            $table->char('id', 10)->primary();
            
            // Foreign Key to users
            $table->char('user_id', 10);
            
            // Leave Information
            $table->date('start_date');
            $table->date('end_date');
            $table->float('total_days'); // Có thể là 0.5
            $table->text('reason')->nullable();
            
            // Type: annual, sick, unpaid
            $table->string('type', 50);
            
            // Status: new, pending, approved, rejected, cancelled (Default: new)
            $table->string('status', 50)->default('new');
            
            // Timestamps
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable(); // Soft Delete
            
            // Audit Logs
            $table->char('created_by', 10)->nullable();
            $table->char('updated_by', 10)->nullable();
            $table->char('deleted_by', 10)->nullable();
            
            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('user_id'); // Foreign Key index
            $table->index('status'); // For status filtering
            $table->index('start_date'); // For date range queries
            $table->index('end_date'); // For date range queries
            $table->index('deleted_at'); // For soft delete queries
            
            // Composite Index for overlap checking
            $table->index(['user_id', 'start_date', 'end_date'], 'idx_user_date_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
