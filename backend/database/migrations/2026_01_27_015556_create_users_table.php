<?php

/**
 * CreateUsersTable Migration
 * 
 * Tạo bảng users trong database.
 * Bảng này lưu thông tin người dùng và hỗ trợ soft delete.
 * 
 * Columns:
 * - id: CHAR(10) primary key
 * - name, email, password: Thông tin cơ bản
 * - type: 0=Admin, 1=Manager, 2=Employee
 * - Audit fields: created_by, updated_by, deleted_by
 * 
 * @see User
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
        Schema::create('users', function (Blueprint $table) {
            // Primary Key: char(10) thay vì BigInt tự tăng
            $table->char('id', 10)->primary();
            
            // Basic Information
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            
            // User Type: 0=Admin, 1=Manager, 2=Employee (Default: 2)
            $table->tinyInteger('type')->default(2);
            
            // Timestamps
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable(); // Soft Delete
            
            // Audit Logs
            $table->char('created_by', 10)->nullable();
            $table->char('updated_by', 10)->nullable();
            $table->char('deleted_by', 10)->nullable();
            
            // Indexes
            $table->index('type'); // For role filtering
            $table->index('deleted_at'); // For soft delete queries
            
            // Foreign Keys (Self-referencing for audit logs)
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
