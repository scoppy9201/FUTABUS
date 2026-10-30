<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buses', function (Blueprint $table): void {
            $table->json('amenities')->nullable();
            $table->string('wifi_name', 100)->nullable();
            $table->string('wifi_password', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('buses', fn (Blueprint $table) => $table->dropColumn(['amenities', 'wifi_name', 'wifi_password']));
    }
};
