<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth; // Ajoutez cet import !

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si la langue est en session
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } 
        // Sinon, vérifier si l'utilisateur est connecté ET a une préférence
        elseif (Auth::check() && Auth::user()->preferred_language) { // Auth::check() au lieu de auth()->check()
            App::setLocale(Auth::user()->preferred_language);
            Session::put('locale', Auth::user()->preferred_language);
        }

        return $next($request);
    }
}