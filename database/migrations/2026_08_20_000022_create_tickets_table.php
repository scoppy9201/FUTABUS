<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seat_layout_id')->nullable()->constrained()->nullOnDelete();
            $table->string('passenger_name');
            $table->string('passenger_phone', 20)->nullable();
            $table->string('seat_code')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('qr_code')->nullable();
            $table->enum('status', ['active', 'used', 'cancelled', 'refunded'])->default('active');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['ticket_code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};