<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('split_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('ten_nhom', 100);
            $table->text('mo_ta')->nullable();

            // Chế độ nhóm: balance = chia số dư, expense = chia khoản chi, both = cả hai
            $table->enum('che_do', ['balance', 'expense', 'both'])->default('both');

            // Bật/tắt hiển thị số dư cho toàn nhóm (chế độ balance)
            $table->boolean('hien_so_du')->default(false);

            // active = đang hoạt động, archived = đã lưu trữ
            $table->enum('trang_thai', ['active', 'archived'])->default('active');

            $table->timestamps();

            $table->index('created_by');
            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_groups');
    }
};
