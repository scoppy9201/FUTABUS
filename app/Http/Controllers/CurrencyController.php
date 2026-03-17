<?php

namespace App\Http\Controllers;

class CurrencyController extends Controller
{
    public function index()
    {
        return view('currency.index');
    }
}