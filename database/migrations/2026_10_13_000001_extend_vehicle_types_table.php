<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            // Phân loại dòng xe: seater | sleeper | limousine | vip_cabin
            $table->string('vehicle_class', 30)->nullable()->after('name');
            // Sơ đồ chỗ ngồi (giải pháp demo - nhập tay)
            $table->unsignedTinyInteger('floor_count')->default(1)->after('vehicle_class');
            $table->unsignedTinyInteger('seat_rows')->nullable()->after('floor_count');
            $table->unsignedTinyInteger('seat_columns')->nullable()->after('seat_rows');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->dropColumn(['vehicle_class', 'floor_count', 'seat_rows', 'seat_columns']);
        });
    }
};
