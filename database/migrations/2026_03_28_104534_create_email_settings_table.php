<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mail_mailer')->default('smtp');
            $table->string('mail_host')->default('smtp.gmail.com');
            $table->integer('mail_port')->default(587);
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable(); // sẽ encrypt
            $table->string('mail_encryption')->default('tls'); // tls/ssl/null
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
