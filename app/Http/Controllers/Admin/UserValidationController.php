<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;

class UserValidationController extends Controller
{
    public function pendingRequests()
    {
        $pendingUsers = User::where('status', 'pending')
            ->where('role', '!=', 'admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('SuperAdmin/PendingRequests', [
            'pendingUsers' => $pendingUsers
        ]);
    }

    public function approve($id)
    {
        try {
            Log::info('Tentative d\'approbation', [
                'user_id' => $id,
                'admin_id' => Auth::id(),
                'admin_email' => Auth::user()->email
            ]);
            
            $user = User::findOrFail($id);
            
            Log::info('Utilisateur trouvé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'current_status' => $user->status
            ]);
            
            if ($user->status === 'pending') {
                $user->status = 'approved';
                $user->save();
                
                // ⚠️ Envoi de l'email de confirmation
                $user->notify(new AccountApprovedNotification());
                
                Log::info('Approbation réussie et email envoyé', [
                    'user_id' => $user->id,
                    'new_status' => $user->status
                ]);
                
                return redirect()->back()->with('success', "Le compte de {$user->name} a été approuvé et un email de confirmation a été envoyé.");
            }
            
            return redirect()->back()->with('error', 'Cette demande ne peut pas être approuvée');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'approbation', [
                'error' => $e->getMessage(),
                'user_id' => $id
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors de l\'approbation: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->status === 'pending') {
                $user->status = 'rejected';
                $user->save();
                
                // Optionnel : récupérer une raison depuis la requête
                $raison = $request->input('raison');
                
                // Envoi de l'email de refus
                $user->notify(new AccountRejectedNotification($raison));
                
                return redirect()->back()->with('success', "Le compte de {$user->name} a été refusé et un email a été envoyé.");
            }
            
            return redirect()->back()->with('error', 'Cette demande ne peut pas être refusée');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors du refus');
        }
    }

    public function approvedUsers()
    {
        $users = User::where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('SuperAdmin/Users', [
            'users' => $users
        ]);
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->id === Auth::id()) {
                return redirect()->back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
            }
            
            $user->delete();
            
            return redirect()->back()->with('success', 'Utilisateur supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression');
        }
    }
}