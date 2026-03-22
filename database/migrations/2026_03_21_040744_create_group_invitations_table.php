<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                  ->constrained('split_groups')
                  ->onDelete('cascade');
            $table->foreignId('invited_by')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Email người được mời (phải là email đã đăng ký trong hệ thống)
            $table->string('email', 100);

            // Token ngẫu nhiên để tạo link xác nhận — tương tự password_reset_tokens
            $table->string('token', 64)->unique();

            // pending = chờ, accepted = đã chấp nhận, declined = từ chối, expired = hết hạn
            $table->enum('trang_thai', ['pending', 'accepted', 'declined', 'expired'])
                  ->default('pending');

            // Hết hạn sau 48 giờ
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // Mỗi email chỉ có 1 lời mời pending trong 1 nhóm tại 1 thời điểm
            $table->unique(['group_id', 'email', 'trang_thai']);
            $table->index('token');
            $table->index(['group_id', 'trang_thai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_invitations');
    }
};
