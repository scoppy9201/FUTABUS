<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeaturedArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = json_decode(
            file_get_contents(database_path('seeders/data/featured-articles.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $timestamp = now();
        $rows = array_map(static fn (array $article): array => [
            ...$article,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $articles);

        if ($rows === []) {
            throw new \RuntimeException('No featured article metadata seed rows were found.');
        }

        DB::table('featured_articles')->upsert(
            $rows,
            ['slug'],
            ['title', 'image', 'published_at', 'intro', 'category', 'news_order', 'promotion_order', 'updated_at'],
        );
    }
}
