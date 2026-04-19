<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->enum('loai_thoi_gian', ['thang', 'ngay'])
                  ->default('thang')
                  ->after('trang_thai');

            $table->date('ngay_bat_dau')
                  ->nullable()
                  ->after('loai_thoi_gian');

            $table->date('ngay_ket_thuc')
                  ->nullable()
                  ->after('ngay_bat_dau');

            $table->boolean('tu_dong_reset')
                  ->default(true)
                  ->after('ngay_ket_thuc');

            $table->boolean('da_het_han')
                  ->default(false)
                  ->after('tu_dong_reset');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn([
                'loai_thoi_gian',
                'ngay_bat_dau',
                'ngay_ket_thuc',
                'tu_dong_reset',
                'da_het_han',
            ]);
        });
    }
};
