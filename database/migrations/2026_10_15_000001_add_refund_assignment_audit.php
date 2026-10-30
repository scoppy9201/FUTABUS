<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_refund_events', function (Blueprint $table) {
            $table->string('action', 16)->nullable();
            $table->foreignId('previous_assigned_to')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_refund_events', function (Blueprint $table) {
            $table->dropForeign(['previous_assigned_to']);
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['action', 'previous_assigned_to', 'assigned_to']);
        });
    }
};
