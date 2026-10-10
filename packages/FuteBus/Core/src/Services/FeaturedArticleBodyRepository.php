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

        if (! preg_match('/^[a-z0-9-]+$/', $slug)) {
            return null;
        }

        $file = database_path('seeders/data/featured-article-bodies/'.$slug.'.html');

        return is_file($file) ? trim(file_get_contents($file)) : null;
    }
}
