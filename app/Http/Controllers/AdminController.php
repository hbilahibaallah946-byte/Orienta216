<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // Ajouter cet import
use Inertia\Inertia;
use Carbon\Carbon;




class AdminController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('SuperAdmin/Dashboard', [
            'totalUsers' => User::where('status', 'approved')->count(),
            'totalEtudiants' => User::where('role', 'etudiant')->where('status', 'approved')->count(),
            'totalConseillers' => User::where('role', 'conseiller')->where('status', 'approved')->count(),
            'totalFilieres' => Filiere::count(),
            'recentUsers' => User::where('status', 'approved')->latest()->take(5)->get(),
            'pendingCount' => User::where('status', 'pending')->count(),
        ]);
    }

    




    // ... vos autres méthodes ...

    public function statistiques()
    {
        // Statistiques de base
        $totalUsers = User::where('status', 'approved')->count();
        $etudiants = User::where('role', 'etudiant')->where('status', 'approved')->count();
        $conseillers = User::where('role', 'conseiller')->where('status', 'approved')->count();
        $filieres = Filiere::count();
        
        // Calcul des évolutions (comparaison avec le mois dernier)
        $moisDernier = Carbon::now()->subMonth();
        $moisActuel = Carbon::now();
        
        // Évolution des étudiants
        $etudiantsMoisDernier = User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$moisDernier->startOfMonth(), $moisDernier->endOfMonth()])
            ->count();
        
        $etudiantsMoisActuel = User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$moisActuel->startOfMonth(), $moisActuel->endOfMonth()])
            ->count();
        
        $etudiantsGrowth = $etudiantsMoisDernier > 0 
            ? round((($etudiantsMoisActuel - $etudiantsMoisDernier) / $etudiantsMoisDernier) * 100, 2)
            : ($etudiantsMoisActuel > 0 ? 100 : 0);
        
        // Évolution des conseillers
        $conseillersMoisDernier = User::where('role', 'conseiller')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$moisDernier->startOfMonth(), $moisDernier->endOfMonth()])
            ->count();
        
        $conseillersMoisActuel = User::where('role', 'conseiller')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$moisActuel->startOfMonth(), $moisActuel->endOfMonth()])
            ->count();
        
        $conseillersGrowth = $conseillersMoisDernier > 0 
            ? round((($conseillersMoisActuel - $conseillersMoisDernier) / $conseillersMoisDernier) * 100, 2)
            : ($conseillersMoisActuel > 0 ? 100 : 0);
        
        // Évolution des utilisateurs totaux
        $usersMoisDernier = User::where('status', 'approved')
            ->whereBetween('created_at', [$moisDernier->startOfMonth(), $moisDernier->endOfMonth()])
            ->count();
        
        $usersMoisActuel = User::where('status', 'approved')
            ->whereBetween('created_at', [$moisActuel->startOfMonth(), $moisActuel->endOfMonth()])
            ->count();
        
        $usersGrowth = $usersMoisDernier > 0 
            ? round((($usersMoisActuel - $usersMoisDernier) / $usersMoisDernier) * 100, 2)
            : ($usersMoisActuel > 0 ? 100 : 0);
        
        // Évolution des filières
        $filieresMoisDernier = Filiere::whereBetween('created_at', [$moisDernier->startOfMonth(), $moisDernier->endOfMonth()])->count();
        $filieresMoisActuel = Filiere::whereBetween('created_at', [$moisActuel->startOfMonth(), $moisActuel->endOfMonth()])->count();
        
        $filieresGrowth = $filieresMoisDernier > 0 
            ? round((($filieresMoisActuel - $filieresMoisDernier) / $filieresMoisDernier) * 100, 2)
            : ($filieresMoisActuel > 0 ? 100 : 0);
        
        // Inscriptions du mois
        $inscriptionsMois = User::where('status', 'approved')
            ->whereBetween('created_at', [$moisActuel->startOfMonth(), $moisActuel->endOfMonth()])
            ->count();
        
        // Demandes en attente
        $enAttente = User::where('status', 'pending')->count();
        
        // Étudiants en attente de réponse (exemple: ceux qui ont postulé mais pas encore traités)
        $enAttenteReponse = User::where('role', 'etudiant')
            ->where('status', 'pending')
            ->count();
        
        // Évolution des inscriptions (8 dernières semaines)
        $evolution = [
            'labels' => [],
            'etudiants' => [],
            'conseillers' => []
        ];
        
        for ($i = 7; $i >= 0; $i--) {
            $semaine = Carbon::now()->subWeeks($i);
            $debutSemaine = $semaine->copy()->startOfWeek();
            $finSemaine = $semaine->copy()->endOfWeek();
            
            $evolution['labels'][] = 'S' . $semaine->weekOfYear;
            $evolution['etudiants'][] = User::where('role', 'etudiant')
                ->where('status', 'approved')
                ->whereBetween('created_at', [$debutSemaine, $finSemaine])
                ->count();
            $evolution['conseillers'][] = User::where('role', 'conseiller')
                ->where('status', 'approved')
                ->whereBetween('created_at', [$debutSemaine, $finSemaine])
                ->count();
        }
        
        // Répartition par filière (top 5)
        $filieresRepartition = Filiere::withCount(['etudiants' => function($query) {
            $query->where('status', 'approved');
        }])
        ->orderBy('etudiants_count', 'desc')
        ->limit(5)
        ->get()
        ->map(function($filiere) {
            return [
                'label' => $filiere->nom,
                'valeur' => $filiere->etudiants_count
            ];
        });
        
        // Si pas de données, mettre des valeurs par défaut
        if ($filieresRepartition->isEmpty()) {
            $filieresRepartition = collect([
                ['label' => 'Aucune donnée', 'valeur' => 0]
            ]);
        }
        
        return Inertia::render('SuperAdmin/Statistiques', [
            'stats' => [
                'users' => $totalUsers,
                'usersGrowth' => $usersGrowth,
                'etudiants' => $etudiants,
                'etudiantsGrowth' => $etudiantsGrowth,
                'conseillers' => $conseillers,
                'conseillersGrowth' => $conseillersGrowth,
                'filieres' => $filieres,
                'filieresGrowth' => $filieresGrowth,
                'inscriptionsMois' => $inscriptionsMois,
                'enAttente' => $enAttente,
                'enAttenteReponse' => $enAttenteReponse,
                'evolution' => $evolution,
                'filieresRepartition' => [
                    'labels' => $filieresRepartition->pluck('label')->toArray(),
                    'valeurs' => $filieresRepartition->pluck('valeur')->toArray()
                ]
            ]
        ]);
    }


    public function filieres()
    {
        return Inertia::render('SuperAdmin/Filieres', [
            'filieres' => Filiere::all()
        ]);
    }

    public function etudiants()
    {
        return Inertia::render('SuperAdmin/Etudiants', [
            'etudiants' => User::where('role', 'etudiant')->where('status', 'approved')->get()
        ]);
    }

    public function users()
    {
        return Inertia::render('SuperAdmin/Users', [
            'users' => User::where('status', 'approved')->get()
        ]);
    }

    public function createUser()
    {
        return Inertia::render('SuperAdmin/CreateUser');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,conseiller,etudiant',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'approved',
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Utiliser Auth::id() au lieu de auth()->id()
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