<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_id')
                  ->constrained('money_wallets')
                  ->onDelete('cascade');

            $table->decimal('so_du_truoc', 18, 2);
            $table->decimal('so_du_sau', 18, 2);
            $table->decimal('chenh_lech', 18, 2);  // dương = tăng, âm = giảm

            // Giao dịch ADJUSTMENT được tạo tự động
            $table->foreignId('transaction_id')
                  ->nullable()
                  ->constrained('transactions')
                  ->onDelete('set null');

            $table->string('ly_do', 255)->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_adjustments');
    }
};
