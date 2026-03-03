<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Migration tạo bảng users */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Khóa chính dạng CHAR(10)
            $table->char('id', 10)->primary();
            
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            
            // Loại user: 0=Admin, 1=Manager, 2=Employee
            $table->tinyInteger('type')->default(2);
            
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable(); // Xóa mềm
            
            // Trường audit: ai tạo/sửa/xóa
            $table->char('created_by', 10)->nullable();
            $table->char('updated_by', 10)->nullable();
            $table->char('deleted_by', 10)->nullable();
            
            $table->index('type');
            $table->index('deleted_at');
            
            // Khóa ngoại tự tham chiếu cho trường audit
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
