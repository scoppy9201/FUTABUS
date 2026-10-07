<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100)->unique();
            $t->string('description', 500)->nullable();
            $t->boolean('is_required')->default(false);
            $t->string('status', 20)->default('active'); // active|inactive
            $t->timestamps();
        });

        Schema::create('document_type_fields', function (Blueprint $t) {
            $t->id();
            $t->foreignId('document_type_id')->constrained('document_types')->cascadeOnDelete();
            $t->string('field_key', 50);
            $t->string('label', 100);
            $t->string('field_type', 20)->default('text'); // text|number|date
            $t->boolean('is_required')->default(true);
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
            $t->unique(['document_type_id', 'field_key']);
        });

        Schema::create('vehicle_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bus_id')->constrained('buses')->cascadeOnDelete();
            $t->foreignId('document_type_id')->constrained('document_types');
            $t->foreignId('renewed_from_id')->nullable()->constrained('vehicle_documents')->nullOnDelete();
            $t->date('issued_date');
            $t->date('expiry_date');
            $t->json('field_values')->nullable(); // snapshot [{key,label,type,value}]
            $t->string('status', 20)->default('active'); // active|inactive
            $t->timestamps();
            $t->index(['bus_id', 'document_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_documents');
        Schema::dropIfExists('document_type_fields');
        Schema::dropIfExists('document_types');
    }
};