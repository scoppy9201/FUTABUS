<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sepay_payment_intents', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->string('code', 14)->unique();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->json('seat_ids');
            $table->json('snapshot');
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['trip_id', 'status', 'expires_at']);
        });

        Schema::create('sepay_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sepay_id')->unique();
            $table->foreignId('payment_intent_id')->nullable()
                ->constrained('sepay_payment_intents')->nullOnDelete();
            $table->string('code', 64)->nullable();
            $table->string('account_number', 32)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 24);
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sepay_transactions');
        Schema::dropIfExists('sepay_payment_intents');
    }
};
