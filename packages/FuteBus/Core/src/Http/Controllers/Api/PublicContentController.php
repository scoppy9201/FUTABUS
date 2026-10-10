<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Controllers\Api;

use FuteBus\Core\Models\BranchRegion;
use FuteBus\Core\Models\BusRoute;
use FuteBus\Core\Models\FaqCategory;
use FuteBus\Core\Services\FeaturedArticleBodyRepository;
use FuteBus\Core\Services\FeaturedNewsCatalog;
use FuteBus\Core\Services\FeaturedPromotionCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class PublicContentController extends Controller
{
    public function news(Request $request, FeaturedNewsCatalog $news): JsonResponse
    {
        $filters = $request->validate([
            'q'        => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);
        $articles = $news->all()
            ->when(filled($filters['category'] ?? null), fn ($items) => $items->where('category', $filters['category']))
            ->when(filled($filters['q'] ?? null), fn ($items) => $items->filter(
                fn (array $article) => Str::contains(
                    Str::lower($article['title'].' '.$article['intro']),
                    Str::lower($filters['q']),
                ),
            ))
            ->values();

        return response()->json(['data' => $articles]);
    }

    public function article(
        string $slug,
        FeaturedNewsCatalog $news,
        FeaturedPromotionCatalog $promotions,
        FeaturedArticleBodyRepository $bodies,
    ): JsonResponse {
        $article = $news->find($slug) ?? $promotions->find($slug);
        abort_if($article === null, 404);

        return response()->json(['data' => [...$article, 'body_html' => $bodies->find($slug)]]);
    }

    public function faqCategories(): JsonResponse
    {
        $categories = FaqCategory::active()->orderBy('sort_order')->get()
            ->map(fn (FaqCategory $category): array => [
                'slug'        => $category->slug,
                'name'        => $category->localized('name'),
                'description' => $category->localized('description'),
                'image'       => $category->image,
            ]);

        return response()->json(['data' => $categories]);
    }

    public function faqCategory(FaqCategory $category): JsonResponse
    {
        abort_unless($category->is_active, 404);

        $questions = $category->questions()->active()->orderBy('sort_order')->get()
            ->map(fn ($question): array => [
                'question' => $question->localizedQuestion(),
                'answer'   => $question->localizedAnswer(),
            ]);

        return response()->json(['data' => [
            'slug'      => $category->slug,
            'name'      => $category->localized('name'),
            'questions' => $questions,
        ]]);
    }

    public function branches(): JsonResponse
    {
        $regions = BranchRegion::active()
            ->with(['offices' => fn ($query) => $query->active()->orderBy('sort_order')])
            ->orderBy('sort_order')->get()
            ->map(fn (BranchRegion $region): array => [
                'slug'    => $region->slug,
                'name'    => $region->localizedName(),
                'offices' => $region->offices->map(fn ($office): array => [
                    'name'    => $office->localized('name'),
                    'address' => $office->localized('address'),
                    'phone'   => $office->phone,
                ]),
            ]);

        return response()->json(['data' => $regions]);
    }

    public function schedules(): JsonResponse
    {
        $routes = BusRoute::active()->publicSchedule()->scheduleOrder()->get()
            ->map(fn (BusRoute $route): array => [
                'id'               => $route->id,
                'origin'           => $route->origin_city,
                'destination'      => $route->destination_city,
                'vehicle_type'     => $route->vehicle_type,
                'distance_km'      => $route->distance_km,
                'duration_minutes' => $route->duration_minutes,
            ]);

        return response()->json(['data' => $routes]);
    }
}
