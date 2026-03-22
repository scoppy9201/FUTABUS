<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Đề xuất chia khoản chi (Chế độ 2 — hướng 1: nhập khoản chi thực tế)
        Schema::create('group_expense_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                  ->constrained('split_groups')
                  ->onDelete('cascade');
            $table->foreignId('proposed_by')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Danh mục tuỳ chọn — link sang categories của người đề xuất
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->onDelete('set null');

            $table->string('mo_ta', 255);
            $table->decimal('tong_tien', 15, 2);
            $table->date('ngay_chi');

            // equal = chia đều, custom = nhập tay, percentage = theo %
            $table->enum('kieu_chia', ['equal', 'custom', 'percentage'])->default('equal');

            // pending = chờ duyệt, approved = thực hiện, rejected = từ chối, cancelled = huỷ
            $table->enum('trang_thai', ['pending', 'approved', 'rejected', 'cancelled'])
                  ->default('pending');

            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'trang_thai']);
            $table->index(['group_id', 'ngay_chi']);
        });

        // Chi tiết từng người trong đề xuất khoản chi
        Schema::create('group_expense_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')
                  ->constrained('group_expense_proposals')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Số tiền người này phải chịu trong khoản chi chung
            $table->decimal('so_tien', 15, 2)->default(0);

            // Tỷ lệ % (dùng khi kieu_chia = percentage)
            $table->decimal('ty_le', 5, 2)->nullable();

            // Transaction CHI được tạo sau khi thực hiện
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

        // Ghi nợ thẳng (Chế độ 2 — hướng 2: A nợ B bao nhiêu)
        Schema::create('group_expense_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                  ->constrained('split_groups')
                  ->onDelete('cascade');

            // Người cho nợ (chủ nợ)
            $table->foreignId('chu_no_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Người đang nợ
            $table->foreignId('nguoi_no_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->decimal('so_tien', 15, 2);
            $table->string('ghi_chu', 255)->nullable();

            // pending = chờ xác nhận, confirmed = đã xác nhận (cả 2 bên), settled = đã thanh toán
            $table->enum('trang_thai', ['pending', 'confirmed', 'settled'])->default('pending');

            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'trang_thai']);
            $table->index(['chu_no_id', 'nguoi_no_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_expense_splits');
        Schema::dropIfExists('group_expense_proposals');
        Schema::dropIfExists('group_expense_debts');
    }
};
