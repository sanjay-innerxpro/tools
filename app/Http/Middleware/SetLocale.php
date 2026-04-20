<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    private const SUPPORTED = ['en','hi','es','fr','zh','ar','pt','de','ja','ru'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->cookie('locale', 'en');

        if (in_array($locale, self::SUPPORTED, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
