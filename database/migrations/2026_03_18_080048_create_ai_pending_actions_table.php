<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_pending_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique(); 
            $table->string('action');                       
            $table->json('data');                            
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_pending_actions');
    }
};