<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('route_stops', function (Blueprint $t) {
        $t->id();
        $t->foreignId('route_id')->constrained()->cascadeOnDelete();
        $t->string('name', 150);
        $t->string('address', 255)->nullable();
        $t->unsignedSmallInteger('stop_order');
        $t->unsignedSmallInteger('offset_minutes')->default(0); // phút kể từ giờ xuất bến
        $t->string('stop_type', 10)->default('both');           // pickup|dropoff|both
        $t->string('status', 20)->default('active');            // active|inactive
        $t->timestamps();
        $t->index(['route_id', 'stop_order']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('route_stops');
}
};
