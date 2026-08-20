<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seat_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained()->cascadeOnDelete();
            $table->string('seat_code');
            $table->unsignedTinyInteger('row_number');
            $table->unsignedTinyInteger('column_number');
            $table->enum('seat_type', ['sleeper', 'semi_sleeper', 'seat'])->default('sleeper');
            $table->enum('deck', ['lower', 'upper'])->default('lower');
            $table->decimal('price_multiplier', 5, 2)->default(1.00);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['bus_id', 'seat_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_layouts');
    }
};