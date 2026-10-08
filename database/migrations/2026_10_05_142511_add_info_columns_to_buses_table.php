<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('buses', function (Blueprint $table) {
        $table->string('color', 50)->nullable()->after('license_plate');
        $table->string('chassis_number', 50)->nullable()->unique()->after('color');
        $table->string('brand', 100)->nullable()->after('chassis_number');
        $table->unsignedSmallInteger('manufacture_year')->nullable()->after('brand');
        $table->string('bus_type')->nullable()->change(); 
    });
}

public function down(): void
{
    Schema::table('buses', function (Blueprint $table) {
        $table->dropUnique(['chassis_number']);
        $table->dropColumn(['color', 'chassis_number', 'brand', 'manufacture_year']);
    });
}
};
