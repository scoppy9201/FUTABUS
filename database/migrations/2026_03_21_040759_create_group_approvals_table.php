<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng đồng thuận dùng chung cho cả balance_proposal và expense_proposal
        // Dùng polymorphic để không phải tạo 2 bảng approval riêng
        Schema::create('group_approvals', function (Blueprint $table) {
            $table->id();

            // Polymorphic: approvable_type = 'balance_proposal' | 'expense_proposal'
            //              approvable_id   = id của proposal tương ứng
            $table->morphs('approvable');

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // approved = đồng ý, rejected = từ chối
            $table->enum('quyet_dinh', ['approved', 'rejected']);

            // Lý do từ chối (tuỳ chọn)
            $table->string('ghi_chu', 255)->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Mỗi user chỉ có 1 quyết định cho 1 proposal
            // Lưu ý: morphs() đã tự tạo index cho (approvable_type, approvable_id) rồi
            $table->unique(['approvable_type', 'approvable_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_approvals');
    }
};
