<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('split_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                  ->constrained('split_groups')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // admin = người tạo/quản lý, member = thành viên thường
            $table->enum('vai_tro', ['admin', 'member'])->default('member');

            // active = đang trong nhóm, left = đã rời (giữ lại để không mất lịch sử)
            $table->enum('trang_thai', ['active', 'left'])->default('active');

            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            // 1 user chỉ có 1 record active trong 1 nhóm
            $table->unique(['group_id', 'user_id']);
            $table->index('group_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_group_members');
    }
};
