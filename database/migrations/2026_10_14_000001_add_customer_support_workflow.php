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
            if (! Schema::hasColumn('customer_feedback_reports', 'assigned_bus_company_id')) {
                $table->foreignId('assigned_bus_company_id')->nullable()->constrained('bus_companies')->nullOnDelete();
            }
            if (! Schema::hasColumn('customer_feedback_reports', 'assigned_staff_id')) {
                $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('customer_feedback_reports', 'version')) {
                $table->unsignedInteger('version')->default(0);
            }
            if (! Schema::hasIndex('customer_feedback_reports', 'feedback_support_company_status')) {
                $table->index(['assigned_bus_company_id', 'status'], 'feedback_support_company_status');
            }
        });
        Schema::create('customer_support_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('report_id')->constrained('customer_feedback_reports')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 120);
            $table->string('kind', 20);
            $table->string('previous_status', 20);
            $table->string('status', 20);
            $table->text('response')->nullable();
            $table->foreignId('previous_bus_company_id')->nullable()->constrained('bus_companies')->nullOnDelete();
            $table->foreignId('bus_company_id')->nullable()->constrained('bus_companies')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->uuid('idempotency_key')->nullable();
            $table->string('request_hash', 64)->nullable();
            $table->timestamp('created_at');
            $table->unique(['report_id', 'version']);
            $table->unique(['report_id', 'idempotency_key'], 'feedback_event_request_unique');
        });
        foreach (['view' => 'View customer support', 'respond' => 'Respond to customer support', 'assign' => 'Assign customer support'] as $action => $name) {
            DB::table('permissions')->insertOrIgnore([
                'slug'         => 'support.'.$action, 'name' => $name, 'group' => 'support',
                'display_name' => $name, 'display_group' => 'Hỗ trợ khách hàng',
                'is_active'    => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_support_events');
        Schema::table('customer_feedback_reports', function (Blueprint $table): void {
            $table->dropIndex('feedback_support_company_status');
            $table->dropConstrainedForeignId('assigned_staff_id');
            $table->dropConstrainedForeignId('assigned_bus_company_id');
            $table->dropColumn('version');
        });
        $ids = DB::table('permissions')->whereIn('slug', ['support.view', 'support.respond', 'support.assign'])->pluck('id');
        DB::table('permissions')->whereIn('id', $ids)
            ->whereNotIn('id', DB::table('permission_role')->select('permission_id'))->delete();
    }
};
