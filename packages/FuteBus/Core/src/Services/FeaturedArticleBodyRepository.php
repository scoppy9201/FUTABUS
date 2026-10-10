<?php

declare(strict_types=1);

namespace FuteBus\Core\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeaturedArticleBodyRepository
{
    public function find(string $slug): ?string
    {
        if (Schema::hasTable('featured_article_bodies')) {
            $body = DB::table('featured_article_bodies')
                ->where('slug', $slug)
                ->value('body_html');

            if ($body !== null) {
                return $body;
            }
        }

        // The bundled seed source keeps article pages available before the seeder is run.
        $articles = json_decode(
            file_get_contents(database_path('seeders/data/featured-article-bodies.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        return $articles[$slug] ?? null;
    }
}
