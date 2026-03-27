<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('loai', [
                'transaction_created',
                'transaction_updated',
                'transaction_deleted',
                'wallet_warning',
                'wallet_exceeded',
                'group_invited',
                'group_joined',
                'group_left',
                'group_removed',
                'group_promoted',
                'group_demoted',
                'balance_proposed',
                'balance_approved',
                'balance_rejected',
                'balance_executed',
                'expense_proposed',
                'expense_approved',
                'expense_rejected',
                'expense_executed',
                'debt_recorded',
                'debt_settled',
                'system',
            ]);

            // "Nguyễn Văn A" hoặc "Hệ thống"
            $table->string('tieu_de', 100);

            // "đã tạo giao dịch chi 500,000đ"
            $table->string('noi_dung', 255);

            // URL redirect khi click
            $table->string('url')->nullable();

            // Người thực hiện (null = hệ thống)
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();

            // Polymorphic entity
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->boolean('da_doc')->default(false);
            $table->timestamp('doc_luc')->nullable();

            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'da_doc']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
