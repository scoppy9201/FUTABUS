<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bus_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bus_company_id')->constrained()->cascadeOnDelete();
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->decimal('price', 12, 2);
            $table->enum('status', ['scheduled', 'departed', 'arrived', 'cancelled'])->default('scheduled');
            $table->unsignedInteger('available_seats')->nullable();
            $table->timestamps();

            $table->index(['departure_time', 'route_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};