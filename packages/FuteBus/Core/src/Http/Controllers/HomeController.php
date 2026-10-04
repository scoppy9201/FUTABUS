<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Controllers;

use FuteBus\Core\Models\BranchRegion;
use FuteBus\Core\Models\BusRoute;
use FuteBus\Core\Models\ContactMessage;
use FuteBus\Core\Models\FaqCategory;
use FuteBus\Core\Models\NewsArticle;
use FuteBus\Core\Models\NewsCategory;
use FuteBus\Core\Models\Promotion;
use FuteBus\Core\Services\HomeService;
use FuteBus\Core\Services\BookingLocationCatalog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeService $homeService,
        private readonly BookingLocationCatalog $bookingLocationCatalog,
    ) {}

    public function index()
    {
        $promotions = Promotion::active()->ordered()->get();

        $popularRoutes = $this->homeService->getPopularRoutes();

        $bookingLocations = $this->bookingLocationCatalog->all();

        $newsArticles = NewsArticle::published()
            ->homepageOrder()
            ->limit(6)
            ->get();

        return view('core::home', [
            'promotions'     => $promotions,
            'popularRoutes'  => $popularRoutes,
            'bookingLocations' => $bookingLocations,
            'newsArticles'   => $newsArticles,
        ]);
    }

    public function about()
    {
        return view('core::about');
    }

    public function bookingGuide()
    {
        return view('core::booking-guide');
    }

    public function privacy()
    {
        return view('core::privacy');
    }

    public function payment()
    {
        return view('core::payment');
    }

    public function pricing()
    {
        return view('core::pricing');
    }

    public function refund()
    {
        return view('core::refund');
    }

    public function terms()
    {
        return view('core::terms');
    }

    public function transactionConditions()
    {
        return view('core::transaction-conditions');
    }

    public function serviceConditions()
    {
        return view('core::service-conditions');
    }

    public function faq()
    {
        $categories = FaqCategory::active()->orderBy('sort_order')->get();

        return view('core::faq', ['categories' => $categories]);
    }

    public function faqCategory(FaqCategory $category)
    {
        abort_unless($category->is_active, 404);

        $questions = $category->questions()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($question) => [
                'question' => $question->localizedQuestion(),
                'answer'   => $question->localizedAnswer(),
            ])
            ->values();

        return view('core::faq-category', compact('category', 'questions'));
    }

    public function complaint()
    {
        return view('core::complaint');
    }

    public function customerSupport()
    {
        return view('core::customer-support');
    }

    public function branches()
    {
        $regions = BranchRegion::active()
            ->with(['offices' => fn ($query) => $query->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return view('core::branches', ['regions' => $regions]);
    }

    public function schedules()
    {
        $scheduleGroups = BusRoute::active()
            ->publicSchedule()
            ->scheduleOrder()
            ->get()
            ->groupBy('schedule_group')
            ->map(fn ($routes) => $routes->map(fn (BusRoute $route) => [
                'from'     => $route->origin_city,
                'to'       => $route->destination_city,
                'vehicle'  => $route->vehicle_type,
                'distance' => $route->distance_km,
                'hours'    => round($route->duration_minutes / 60, 1),
            ])->values())
            ->values();

        return view('core::schedules', compact('scheduleGroups'));
    }

    public function news(Request $request)
    {
        $category = $request->string('category')->trim()->toString();
        $search = $request->string('q')->trim()->toString();

        $categories = NewsCategory::active()->orderBy('sort_order')->get();
        $query = NewsArticle::published()
            ->with('category')
            ->when($category, fn ($builder) => $builder->whereHas(
                'category',
                fn ($categoryQuery) => $categoryQuery->where('slug', $category)->where('is_active', true),
            ))
            ->when($search, fn ($builder) => $builder->where(function ($searchQuery) use ($search) {
                $searchQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            }));

        $featuredArticles = (clone $query)
            ->where('is_featured', true)
            ->homepageOrder()
            ->limit(5)
            ->get();

        $spotlightArticles = NewsArticle::published()
            ->with('category')
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery
                ->where('slug', 'futa-city-bus')
                ->where('is_active', true))
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $articles = $query
            ->orderByDesc('published_at')
            ->paginate(6)
            ->withQueryString();

        return view('core::news', compact(
            'articles',
            'categories',
            'category',
            'featuredArticles',
            'search',
            'spotlightArticles',
        ));
    }

    public function contact()
    {
        return view('core::contact');
    }

    public function invoice()
    {
        return view('core::invoice');
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate(
            [
                'department' => ['required', 'in:futabus'],
                'name'       => ['required', 'string', 'max:120'],
                'email'      => ['required', 'email', 'max:255'],
                'phone'      => ['required', 'regex:/^[0-9+\s.()-]{8,20}$/'],
                'subject'    => ['required', 'string', 'max:255'],
                'message'    => ['required', 'string', 'max:5000'],
            ],
            __('core::contact.validation'),
            __('core::contact.attributes'),
        );

        ContactMessage::create($validated);

        return back()->with('contact_success', __('core::contact.success'));
    }
}
