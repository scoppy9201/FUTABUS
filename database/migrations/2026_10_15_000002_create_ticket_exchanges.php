<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_exchanges', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('old_ticket_id')->constrained('tickets')->restrictOnDelete();
            $table->foreignId('new_ticket_id')->nullable()->unique()->constrained('tickets')->restrictOnDelete();
            $table->foreignId('new_booking_id')->nullable()->unique()->constrained('bookings')->restrictOnDelete();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->foreignId('target_trip_id')->constrained('trips')->restrictOnDelete();
            $table->foreignId('seat_layout_id')->constrained('seat_layouts')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 16)->default('held');
            $table->unsignedBigInteger('fare');
            $table->unsignedBigInteger('fee')->default(0);
            $table->string('policy_version', 48);
            $table->timestamp('original_departure_at');
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('snapshot');
            $table->timestamps();
            $table->index(['target_trip_id', 'status', 'expires_at']);
            $table->index(['old_ticket_id', 'status']);
        });
    }

    public function down(): void
    {
        if (DB::table('ticket_exchanges')->where('status', 'completed')->exists()) {
            throw new RuntimeException('Preserve completed exchange audit records before rolling back.');
        }
        Schema::dropIfExists('ticket_exchanges');
    }
};
