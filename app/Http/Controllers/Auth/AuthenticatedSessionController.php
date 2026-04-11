<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return back()->withErrors([
                'email' => 'Les informations sont incorrectes.',
            ]);
        }

        // Vérifier le statut
        if ($user->status === 'pending') {
            return back()->withErrors([
                'email' => 'Votre compte est en attente de validation par l\'administrateur.',
            ]);
        }

        if ($user->status === 'rejected') {
            return back()->withErrors([
                'email' => 'Votre demande d\'inscription a été refusée. Contactez l\'administrateur.',
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Les informations sont incorrectes.',
            ]);
        }

        $request->session()->regenerate();

        // Rediriger vers la route dashboard qui redirigera vers le bon dashboard
        return redirect()->route('dashboard');
    }
    
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}