<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_feedback_reports', function (Blueprint $table): void {
            $table->foreignId('department_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->timestamp('transferred_at')->nullable();
        });
        Schema::table('customer_support_events', function (Blueprint $table): void {
            $table->text('internal_note')->nullable();
            $table->string('department_name')->nullable();
        });
    }

    public function down(): void
    {
        DB::table('customer_feedback_reports')->where('status', 'waiting')->update(['status' => 'reviewing']);
        Schema::table('customer_feedback_reports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('department_role_id');
            $table->dropColumn('transferred_at');
        });
        Schema::table('customer_support_events', function (Blueprint $table): void {
            $table->dropColumn(['internal_note', 'department_name']);
        });
    }
};
