<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function update(Request $request)
    {
        // Validation personnalisée
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        
        $user = Auth::user();
        
        // Vérifier le mot de passe actuel avec Hash::check
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect'
            ], 422);
        }
        
        // Vérifier que le nouveau mot de passe est différent
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Le nouveau mot de passe doit être différent de l\'ancien'
            ], 422);
        }
        
        // Mettre à jour le mot de passe
        $user->password = Hash::make($request->password);
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès'
        ]);
    }
}