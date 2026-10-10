<?php

declare(strict_types=1);

namespace FuteBus\Core\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeaturedArticleCatalog
{
    public function all(): Collection
    {
        $articles = collect(json_decode(
            file_get_contents(database_path('seeders/data/featured-articles.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        ))->keyBy('slug');

        if (Schema::hasTable('featured_articles')) {
            DB::table('featured_articles')->get()->each(function (object $article) use ($articles): void {
                $articles->put($article->slug, array_intersect_key(
                    (array) $article,
                    array_flip(['slug', 'title', 'image', 'published_at', 'intro', 'category', 'news_order', 'promotion_order']),
                ));
            });
        }

        return $articles->values();
    }
}
