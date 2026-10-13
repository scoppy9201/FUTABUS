<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            // Số máy xe - bắt buộc nhập
            $table->string('engine_number', 50)->nullable()->after('chassis_number');
            // Năm hết hạn niên hạn (tính từ manufacture_year + 15 hoặc +20 tuỳ dòng xe)
            $table->unsignedSmallInteger('expiration_year')->nullable()->after('manufacture_year');
            // Tình trạng giấy tờ tổng hợp (cache, cập nhật bởi job/trigger)
            // ok | warning | missing | expired | age_expired | inactive
            $table->string('doc_status', 20)->default('ok')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->dropColumn(['engine_number', 'expiration_year', 'doc_status']);
        });
    }
};
