<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * Các trường không muốn trim
     *
     * @var array<int, string>
     */
    protected $except = [
        'password',
        'password_confirmation',
        'current_password',
    ];
}
