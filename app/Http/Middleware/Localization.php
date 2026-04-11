<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        // Récupérer la langue depuis la session, le cookie, la base de données ou la valeur par défaut
        $locale = Session::get('locale');
        
        if (!$locale) {
            $locale = Cookie::get('locale');
        }
        
        if (!$locale && Auth::check() && Auth::user()->language) {
            $locale = Auth::user()->language;
        }
        
        if (!$locale) {
            $locale = 'fr';
        }
        
        // Appliquer la langue
        App::setLocale($locale);
        
        return $next($request);
    }
}