<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('featured_articles', function (Blueprint $table): void {
            $table->string('slug')->primary();
            $table->string('title');
            $table->string('image');
            $table->string('published_at', 40);
            $table->text('intro');
            $table->string('category', 40);
            $table->unsignedSmallInteger('news_order')->nullable();
            $table->unsignedSmallInteger('promotion_order')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_articles');
    }
};
