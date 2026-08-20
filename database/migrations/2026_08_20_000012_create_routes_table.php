<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_company_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('origin_city');
            $table->string('origin_station')->nullable();
            $table->string('destination_city');
            $table->string('destination_station')->nullable();
            $table->unsignedInteger('distance_km')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['origin_city', 'destination_city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};