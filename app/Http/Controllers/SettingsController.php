<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings');
    }

    public function update(Request $request)
    {
        // Lưu vào localStorage qua JS, không cần DB
        return response()->json(['success' => true]);
    }
}