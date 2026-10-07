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
        $table->foreignId('vehicle_type_id')->nullable()->after('bus_company_id')
              ->constrained('vehicle_types')->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('buses', function (Blueprint $table) {
        $table->dropConstrainedForeignId('vehicle_type_id');
    });
}
};
