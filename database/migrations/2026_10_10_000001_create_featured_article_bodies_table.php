<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('featured_article_bodies', function (Blueprint $table): void {
            $table->string('slug')->primary();
            $table->longText('body_html');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_article_bodies');
    }
};
