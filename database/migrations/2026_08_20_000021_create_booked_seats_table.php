<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booked_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seat_layout_id')->constrained()->cascadeOnDelete();
            $table->string('seat_code');
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['trip_id', 'seat_layout_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booked_seats');
    }
};