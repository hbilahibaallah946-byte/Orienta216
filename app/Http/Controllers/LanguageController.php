<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;

class LanguageController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'language' => 'required|in:fr,en,ar,es'
        ]);
        
        $language = $request->language;
        
        // Stocker dans la session
        Session::put('locale', $language);
        
        // Stocker dans un cookie pour persistance
        Cookie::queue('locale', $language, 60 * 24 * 30);
        
        // Stocker dans la base de données si l'utilisateur est connecté
        if (Auth::check()) {
            $user = Auth::user();
            $user->language = $language;
            $user->save();
        }
        
        // Appliquer la langue
        App::setLocale($language);
        
        // Rediriger vers la page précédente avec un message flash
        return redirect()->back()->with('success', 'Langue changée avec succès');
    }
    
    public function current()
    {
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
        
        App::setLocale($locale);
        
        return response()->json([
            'language' => $locale
        ]);
    }
}