<?php

declare(strict_types=1);

namespace FuteBus\Core\Services;

use Illuminate\Support\Collection;

class FeaturedPromotionCatalog
{
    public function __construct(private readonly FeaturedArticleCatalog $featuredArticleCatalog) {}

    public function all(): Collection
    {
        return $this->featuredArticleCatalog->all()
            ->filter(fn (array $article): bool => $article['promotion_order'] !== null)
            ->sortBy('promotion_order')
            ->map(fn (array $article): array => array_diff_key($article, array_flip(['news_order', 'promotion_order'])))
            ->values();
    }

    public function find(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }
}
