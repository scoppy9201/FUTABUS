<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // wallet_id nullable → giao dịch cũ không bị ảnh hưởng
            $table->foreignId('money_wallet_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('money_wallets')
                  ->onDelete('set null');

            // Đánh dấu giao dịch chuyển khoản nội bộ để loại khỏi thống kê
            $table->boolean('la_chuyen_vi')->default(false)->after('ghi_chu');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['money_wallet_id']);
            $table->dropColumn(['money_wallet_id', 'la_chuyen_vi']);
        });
    }
};
