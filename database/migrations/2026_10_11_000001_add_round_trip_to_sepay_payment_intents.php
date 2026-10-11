<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sepay_payment_intents', function (Blueprint $table): void {
            $table->foreignId('secondary_trip_id')->nullable()->constrained('trips')->nullOnDelete();
            $table->json('secondary_seat_ids')->nullable();
            $table->foreignId('secondary_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->index(['secondary_trip_id', 'status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('sepay_payment_intents', function (Blueprint $table): void {
            $table->dropIndex(['secondary_trip_id', 'status', 'expires_at']);
            $table->dropConstrainedForeignId('secondary_booking_id');
            $table->dropColumn('secondary_seat_ids');
            $table->dropConstrainedForeignId('secondary_trip_id');
        });
    }
};
