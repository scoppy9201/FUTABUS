<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained()->cascadeOnDelete();
            $table->time('departure_time');
            $table->unsignedSmallInteger('duration_minutes')->default(0);
            $table->json('days_of_week');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 12, 2);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('trip_schedule_id')->nullable()->after('id')
                ->constrained('trip_schedules')->nullOnDelete();
            $table->unique(['trip_schedule_id', 'departure_time']);
            $table->unsignedBigInteger('bus_id')->nullable()->change();
            $table->string('status', 20)->default('scheduled')->change();
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropUnique(['trip_schedule_id', 'departure_time']);
            $table->dropConstrainedForeignId('trip_schedule_id');
        });
        Schema::dropIfExists('trip_schedules');
    }
};
