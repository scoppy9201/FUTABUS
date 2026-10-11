<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_feedback_reports', function (Blueprint $table): void {
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('urgency', 20)->default('complaint');
            $table->string('contact_name', 120)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('truth_confirm')->default(false);
            $table->boolean('allow_contact')->default(false);
            $table->text('response')->nullable();
            $table->timestamp('reviewing_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
        });

        Schema::create('customer_feedback_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('report_id')->constrained('customer_feedback_reports')->cascadeOnDelete();
            $table->string('path')->unique();
            $table->string('original_name');
            $table->string('mime_type', 50);
            $table->unsignedInteger('size_bytes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_feedback_attachments');

        Schema::table('customer_feedback_reports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('booking_id');
            $table->dropColumn([
                'urgency', 'contact_name', 'contact_phone', 'contact_email',
                'truth_confirm', 'allow_contact', 'response', 'reviewing_at', 'resolved_at',
            ]);
        });
    }
};
