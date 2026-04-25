<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;



class AccountController extends Controller
{
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
        
        $user = Auth::user();
        
        // Supprimer toutes les données liées
        $user->moyennes()->delete();
        $user->reponses()->delete();
        
        // Supprimer le compte
        $user->delete();
        
        Auth::logout();
        
        return response()->json([
            'success' => true,
            'message' => 'Compte supprimé avec succès'
        ]);
    }
}