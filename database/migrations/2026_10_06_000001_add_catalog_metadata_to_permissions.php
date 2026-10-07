<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('group');
            $table->string('display_name')->nullable()->after('name');
            $table->string('display_group')->nullable()->after('group');
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table): void {
            $table->dropColumn(['is_active', 'display_name', 'display_group']);
        });
    }
};
