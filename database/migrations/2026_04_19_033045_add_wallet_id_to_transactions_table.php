<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('wallet_id')
                  ->nullable()
                  ->after('category_id');

            $table->foreign('wallet_id')
                  ->references('id')
                  ->on('wallets')
                  ->nullOnDelete(); // budget bị xóa → wallet_id = null, giao dịch vẫn còn
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['wallet_id']);
            $table->dropColumn('wallet_id');
        });
    }
};