<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeaturedArticleBodySeeder extends Seeder
{
    public function run(): void
    {
        $articles = json_decode(
            file_get_contents(database_path('seeders/data/featured-article-bodies.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($articles as $slug => $bodyHtml) {
            DB::table('featured_article_bodies')->updateOrInsert(
                ['slug' => $slug],
                ['body_html' => $bodyHtml, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }
}
