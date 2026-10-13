<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            // Số vị trí không dùng trong sơ đồ (lối đi, ghế tài xế, hàng cuối khác số cột…)
            $table->unsignedTinyInteger('unused_seats')->default(0)->after('seat_columns');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->dropColumn('unused_seats');
        });
    }
};
