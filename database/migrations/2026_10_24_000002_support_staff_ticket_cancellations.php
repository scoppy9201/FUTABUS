<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_cancellations', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('ticket_cancellations')->whereNull('user_id')->orWhereNotNull('actor_id')->exists()) {
            throw new RuntimeException('Preserve guest and staff cancellation audit before rolling back.');
        }
        Schema::table('ticket_cancellations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('actor_id');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
