<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->foreignId('from_wallet_id')
                  ->constrained('money_wallets')
                  ->onDelete('cascade');

            $table->foreignId('to_wallet_id')
                  ->constrained('money_wallets')
                  ->onDelete('cascade');

            $table->decimal('so_tien', 18, 2);
            $table->decimal('phi_chuyen', 18, 2)->default(0); // phí nếu có

            // 2 giao dịch được tạo tự động
            $table->foreignId('from_transaction_id')
                  ->nullable()
                  ->constrained('transactions')
                  ->onDelete('set null');

            $table->foreignId('to_transaction_id')
                  ->nullable()
                  ->constrained('transactions')
                  ->onDelete('set null');

            // Category người dùng chọn
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->onDelete('set null');

            $table->date('ngay_chuyen');
            $table->text('ghi_chu')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'ngay_chuyen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transfers');
    }
};
