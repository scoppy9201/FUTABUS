<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_documents', function (Blueprint $table): void {
            $table->date('expiry_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('vehicle_documents')->whereNull('expiry_date')->exists()) {
            throw new RuntimeException(
                'Non-expiring vehicle documents exist. Move or remove those records before making expiry_date required again.'
            );
        }

        Schema::table('vehicle_documents', function (Blueprint $table): void {
            $table->date('expiry_date')->nullable(false)->change();
        });
    }
};
