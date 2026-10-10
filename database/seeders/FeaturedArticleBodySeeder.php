<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeaturedArticleBodySeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();
        $rows = [];

        foreach (glob(database_path('seeders/data/featured-article-bodies/*.html')) ?: [] as $file) {
            $rows[] = [
                'slug'       => pathinfo($file, PATHINFO_FILENAME),
                'body_html'  => trim(file_get_contents($file)),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if ($rows === []) {
            throw new \RuntimeException('No featured article body seed files were found.');
        }

        DB::table('featured_article_bodies')->upsert($rows, ['slug'], ['body_html', 'updated_at']);
    }
}
