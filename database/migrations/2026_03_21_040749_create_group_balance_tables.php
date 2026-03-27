<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Đề xuất phân phối lại số dư (Chế độ 1)
        Schema::create('group_balance_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                  ->constrained('split_groups')
                  ->onDelete('cascade');
            $table->foreignId('proposed_by')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('mo_ta', 255)->nullable();

            // Snapshot tổng số dư tại thời điểm đề xuất — tránh lệch nếu số dư thay đổi sau
            $table->decimal('tong_so_du', 15, 2)->default(0);

            // pending = chờ duyệt, approved = đã duyệt & thực hiện, rejected = bị từ chối, cancelled = huỷ
            $table->enum('trang_thai', ['pending', 'approved', 'rejected', 'cancelled'])
                  ->default('pending');

            // Khi nào được thực hiện (sau khi toàn bộ đồng ý)
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'trang_thai']);
        });

        // Chi tiết phân chia từng người trong đề xuất số dư
        Schema::create('group_balance_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')
                  ->constrained('group_balance_proposals')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Snapshot số dư hiện tại của user khi đề xuất được tạo
            $table->decimal('so_du_cu', 15, 2)->default(0);

            // Số dư mới mà người chủ trì đề xuất
            $table->decimal('so_du_moi', 15, 2)->default(0);

            // Chênh lệch = so_du_moi - so_du_cu (dương = nhận thêm, âm = bị trừ)
            $table->decimal('chenh_lech', 15, 2)->default(0);

            // Transaction được tạo sau khi thực hiện (THU nếu chenh_lech > 0, CHI nếu < 0)
            $table->foreignId('transaction_id')
                  ->nullable()
                  ->constrained('transactions')
                  ->onDelete('set null');

            // pending = chưa duyệt, approved = đồng ý, rejected = từ chối
            $table->enum('trang_thai_dong_y', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['proposal_id', 'user_id']);
            $table->index('proposal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_balance_splits');
        Schema::dropIfExists('group_balance_proposals');
    }
};
