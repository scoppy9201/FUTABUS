<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sepay_transactions', function (Blueprint $table) {
            $table->string('environment', 12)->nullable();
        });
        Schema::create('ticket_cancellations', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('ticket_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('reason', 40);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('ticket_amount');
            $table->unsignedTinyInteger('fee_percent');
            $table->unsignedBigInteger('fee_amount');
            $table->unsignedBigInteger('refund_amount');
            $table->string('policy_version');
            $table->timestamp('departure_at');
            $table->timestamps();
        });
        Schema::create('ticket_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cancellation_id')->unique()->constrained('ticket_cancellations')->restrictOnDelete();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->string('transaction_id');
            $table->string('idempotency_key')->unique();
            $table->unsignedBigInteger('amount');
            $table->string('environment', 12);
            $table->string('status', 24)->default('queued')->index();
            $table->string('provider_refund_id')->nullable()->unique();
            $table->string('error_code')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_refunds');
        Schema::dropIfExists('ticket_cancellations');
        Schema::table('sepay_transactions', function (Blueprint $table) {
            $table->dropColumn('environment');
        });
    }
};
