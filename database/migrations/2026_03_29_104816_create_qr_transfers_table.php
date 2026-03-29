<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sender_wallet_id')->constrained('money_wallets')->onDelete('cascade');
            $table->foreignId('receiver_wallet_id')->nullable()->constrained('money_wallets')->onDelete('set null');
            $table->decimal('so_tien', 18, 2);
            $table->string('ghi_chu', 255)->nullable();
            $table->string('qr_token', 64)->unique();
            $table->enum('trang_thai', ['pending', 'completed', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_transfers');
    }
};
