<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Controllers;

use FuteBus\Core\Models\NewsArticle;
use FuteBus\Core\Models\Promotion;
use FuteBus\Core\Services\HomeService;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeService $homeService,
    ) {}

    public function index()
    {
        $promotions = Promotion::active()->ordered()->get();

        $popularRoutes = $this->homeService->getPopularRoutes();

        $newsArticles = NewsArticle::published()
            ->homepageOrder()
            ->limit(6)
            ->get();

        return view('core::home', [
            'promotions'    => $promotions,
            'popularRoutes' => $popularRoutes,
            'newsArticles'   => $newsArticles,
        ]);
    }

    public function about()
    {
        return view('core::about');
    }

    public function privacy()
    {
        return view('core::privacy');
    }
}
