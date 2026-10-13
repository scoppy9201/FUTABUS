<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_refunds', function (Blueprint $table) {
            $table->string('processing_mode', 16)->default('automatic');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version')->default(0);
            $table->string('bank_reference', 100)->nullable()->unique();
            $table->timestamp('bank_paid_at')->nullable();
            $table->unsignedBigInteger('confirmed_amount')->nullable();
            $table->text('staff_note')->nullable();
        });
        Schema::create('ticket_refund_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_id')->constrained('ticket_refunds')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->string('from_status', 24);
            $table->string('to_status', 24);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->string('bank_reference', 100)->nullable();
            $table->timestamp('bank_paid_at')->nullable();
            $table->timestamp('created_at');
            $table->unique(['refund_id', 'version']);
        });
        DB::table('ticket_refunds')->where('status', 'queued')->whereNull('provider_refund_id')->whereNull('error_code')
            ->update(['processing_mode' => 'manual', 'status' => 'refund_pending']);
    }

    public function down(): void
    {
        if (DB::table('ticket_refund_events')->exists()) {
            throw new RuntimeException('Manual refund audit records exist. Preserve them before rolling back.');
        }
        DB::table('ticket_refunds')->where('processing_mode', 'manual')->where('status', 'refund_pending')
            ->update(['processing_mode' => 'automatic', 'status' => 'queued']);
        Schema::dropIfExists('ticket_refund_events');
        Schema::table('ticket_refunds', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropUnique(['bank_reference']);
            $table->dropColumn(['processing_mode', 'assigned_to', 'version', 'bank_reference', 'bank_paid_at', 'confirmed_amount', 'staff_note']);
        });
    }
};
