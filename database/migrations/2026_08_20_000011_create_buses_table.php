<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_company_id')->constrained()->cascadeOnDelete();
            $table->string('license_plate')->unique();
            $table->string('name')->nullable();
            $table->unsignedTinyInteger('capacity')->default(45);
            $table->enum('bus_type', ['limousine', 'sleeper', 'standard', 'minivan'])->default('sleeper');
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->integer('seat_rows')->default(11);
            $table->integer('seat_columns')->default(4);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};