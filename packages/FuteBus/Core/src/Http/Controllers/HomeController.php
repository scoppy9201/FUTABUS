<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Controllers;

use FuteBus\Core\Models\Promotion;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $promotions = Promotion::active()->ordered()->get();

        return view('core::home', [
            'promotions' => $promotions,
        ]);
    }
}
