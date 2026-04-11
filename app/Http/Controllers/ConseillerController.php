<?php

namespace App\Http\Controllers;  // ⭐ Vérifiez que c'est exactement ça

use App\Models\ConseillerSetting;
use App\Models\Filiere;
use App\Models\Moyenne;
use App\Models\Question;
use App\Models\Reponse;
use App\Models\User;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ConseillerController extends Controller
{
    
   public function dashboard()
{
    $user = Auth::user();
    
    return Inertia::render('Conseiller/Dashboard', [
        // ⭐ Pour les cartes statistiques
        'totalEtudiants' => User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->count(),
        
        'totalFilieres' => Filiere::count(),
        
        'questionnairesCount' => Questionnaire::where('conseiller_id', $user->id)->count(),
        
        // ⭐ Pour le tableau des derniers étudiants
        'recentEtudiants' => User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at']),
        
        // ⭐ Pour la liste des derniers questionnaires
        'recentQuestionnaires' => Questionnaire::with('etudiant')
            ->where('conseiller_id', $user->id)
            ->latest()
            ->limit(5)
            ->get(),
        
        // ⭐ Pour l'utilisateur connecté
        'conseiller' => $user,
        
        // ⭐ Optionnel : si vous voulez garder les questions pour autre chose
        'questions' => Question::withCount('reponses')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(),
    ]);
}
    
    // ══════════════════════════════════════════════════════════════════════════
    // PARAMÈTRES — 4 endpoints JSON
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * POST /conseiller/parametres/profil
     * Modifie uniquement le nom du conseiller.
     */
    public function updateProfil(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = User::findOrFail(Auth::id());

        $user->update([
            'name' => $request->name
        ]);

        return back()->with('success', 'Nom mis à jour avec succès.');
    }

    /**
     * POST /conseiller/parametres/disponibilite
     */
    public function updateDisponibilite(Request $request)
    {
        $request->validate([
            'jours_disponibles'      => ['required', 'array', 'min:1'],
            'jours_disponibles.*'    => ['string', 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche'],
            'heure_debut'            => ['required', 'date_format:H:i'],
            'heure_fin'              => ['required', 'date_format:H:i', 'after:heure_debut'],
            'max_etudiants_par_jour' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        ConseillerSetting::updateOrCreate(
            ['conseiller_id' => Auth::id()],
            [
                'jours_disponibles'      => $request->jours_disponibles,
                'heure_debut'            => $request->heure_debut,
                'heure_fin'              => $request->heure_fin,
                'max_etudiants_par_jour' => $request->max_etudiants_par_jour,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Disponibilité enregistrée.']);
    }

    /**
     * POST /conseiller/parametres/password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $user = User::findOrFail(Auth::id());

        // Vérifier l'ancien mot de passe
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe actuel est incorrect.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['success' => true, 'message' => 'Mot de passe modifié avec succès.']);
    }

    /**
     * POST /conseiller/parametres/notifications
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'notif_nouveau_message'  => ['boolean'],
            'notif_nouveau_etudiant' => ['boolean'],
            'notif_email'            => ['boolean'],
        ]);

        ConseillerSetting::updateOrCreate(
            ['conseiller_id' => Auth::id()],
            [
                'notif_nouveau_message'  => $request->boolean('notif_nouveau_message'),
                'notif_nouveau_etudiant' => $request->boolean('notif_nouveau_etudiant'),
                'notif_email'            => $request->boolean('notif_email'),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Notifications enregistrées.']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ÉTUDIANTS
    // ══════════════════════════════════════════════════════════════════════════

    public function etudiants()
    {
        $etudiants = User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->with('filiere')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Conseiller/Etudiants', ['etudiants' => $etudiants]);
    }

    public function showEtudiant($id)
    {
        $etudiant = User::where('role', 'etudiant')->where('id', $id)->with('filiere')->firstOrFail();
        $moyennes = Moyenne::where('user_id', $id)->orderBy('created_at', 'desc')->get();

        return Inertia::render('Conseiller/EtudiantDetails', [
            'etudiant' => $etudiant,
            'moyennes' => $moyennes,
            'favoris'  => [],
        ]);
    }

    public function createEtudiant()
    {
        return Inertia::render('Conseiller/CreateEtudiant', ['filieres' => Filiere::all()]);
    }

    public function storeEtudiant(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:6',
            'filiere_id' => 'nullable|exists:filieres,id',
        ]);

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => 'etudiant',
            'status'     => 'pending',
            'filiere_id' => $request->filiere_id ?? null,
        ]);

        return redirect()->route('conseiller.etudiants.index')
            ->with('success', 'Étudiant créé. En attente de validation.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STATISTIQUES - Version complète avec tous les diagrammes
    // ══════════════════════════════════════════════════════════════════════════

   public function statistiques()
{
    // Statistiques de base
    $etudiants = User::where('role', 'etudiant')->where('status', 'approved')->count();
    $filieres = Filiere::count();
    $enAttente = User::where('role', 'etudiant')->where('status', 'pending')->count();
    
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
    
    // Évolution des filières
    $filieresMoisDernier = Filiere::whereBetween('created_at', [$moisDernier->startOfMonth(), $moisDernier->endOfMonth()])->count();
    $filieresMoisActuel = Filiere::whereBetween('created_at', [$moisActuel->startOfMonth(), $moisActuel->endOfMonth()])->count();
    
    $filieresGrowth = $filieresMoisDernier > 0 
        ? round((($filieresMoisActuel - $filieresMoisDernier) / $filieresMoisDernier) * 100, 2)
        : ($filieresMoisActuel > 0 ? 100 : 0);
    
    // Inscriptions du mois
    $inscriptionsMois = User::where('role', 'etudiant')
        ->where('status', 'approved')
        ->whereBetween('created_at', [$moisActuel->startOfMonth(), $moisActuel->endOfMonth()])
        ->count();
    
    // Évolution des inscriptions (8 dernières semaines)
    $evolution = [
        'labels' => [],
        'valeurs' => []
    ];
    
    for ($i = 7; $i >= 0; $i--) {
        $semaine = Carbon::now()->subWeeks($i);
        $debutSemaine = $semaine->copy()->startOfWeek();
        $finSemaine = $semaine->copy()->endOfWeek();
        
        $evolution['labels'][] = 'S' . $semaine->weekOfYear;
        $evolution['valeurs'][] = User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$debutSemaine, $finSemaine])
            ->count();
    }
    
    // ⭐ CORRECTION ICI - Répartition par filière (top 5)
    $filieresRepartition = collect();
    
    // Récupérer toutes les filières avec le nombre d'étudiants
    $toutesFilieres = Filiere::all();
    foreach ($toutesFilieres as $filiere) {
        $count = User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->where('filiere_id', $filiere->id)
            ->count();
        
        if ($count > 0) {
            $filieresRepartition->push([
                'label' => $filiere->specialite ?: $filiere->nom,
                'valeur' => $count
            ]);
        }
    }
    
    // Trier par valeur décroissante et prendre les 5 premiers
    $filieresRepartition = $filieresRepartition->sortByDesc('valeur')->take(5)->values();
    
    if ($filieresRepartition->isEmpty()) {
        $filieresRepartition = collect([
            ['label' => 'Aucune donnée', 'valeur' => 0]
        ]);
    }
    
    // ⭐ CORRECTION ICI - Performance par filière
    $performanceParFiliere = [];
    
    foreach ($toutesFilieres as $filiere) {
        $totalMoyennes = 0;
        $countMoyennes = 0;
        
        $etudiantsFiliere = User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->where('filiere_id', $filiere->id)
            ->get();
        
        foreach ($etudiantsFiliere as $etudiant) {
            $moyennes = $etudiant->moyennes;
            if ($moyennes->count() > 0) {
                $totalMoyennes += $moyennes->avg('moyenne');
                $countMoyennes++;
            }
        }
        
        if ($countMoyennes > 0) {
            $performanceParFiliere[] = [
                'label' => $filiere->specialite ?: $filiere->nom,
                'valeur' => round($totalMoyennes / $countMoyennes, 1)
            ];
        }
    }
    
    if (empty($performanceParFiliere)) {
        $performanceParFiliere = [
            ['label' => 'Aucune donnée', 'valeur' => 0]
        ];
    }
    
    // Calcul de la moyenne générale
    $toutesMoyennes = Moyenne::whereHas('user', function($query) {
        $query->where('role', 'etudiant')->where('status', 'approved');
    })->get();
    
    $moyenneGenerale = $toutesMoyennes->isNotEmpty() 
        ? round($toutesMoyennes->avg('moyenne'), 2) 
        : 0;
    
    // Évolution de la moyenne générale
    $moyennesMoisDernier = Moyenne::whereHas('user', function($query) {
        $query->where('role', 'etudiant')->where('status', 'approved');
    })->whereBetween('created_at', [$moisDernier->startOfMonth(), $moisDernier->endOfMonth()])
    ->get();
    
    $moyennesMoisActuel = Moyenne::whereHas('user', function($query) {
        $query->where('role', 'etudiant')->where('status', 'approved');
    })->whereBetween('created_at', [$moisActuel->startOfMonth(), $moisActuel->endOfMonth()])
    ->get();
    
    $moyenneMoisDernier = $moyennesMoisDernier->isNotEmpty() ? $moyennesMoisDernier->avg('moyenne') : 0;
    $moyenneMoisActuel = $moyennesMoisActuel->isNotEmpty() ? $moyennesMoisActuel->avg('moyenne') : 0;
    
    $moyenneGrowth = $moyenneMoisDernier > 0 
        ? round((($moyenneMoisActuel - $moyenneMoisDernier) / $moyenneMoisDernier) * 100, 2)
        : ($moyenneMoisActuel > 0 ? 100 : 0);
    
    // Taux de réussite
    $etudiantsReussite = 0;
    foreach (User::where('role', 'etudiant')->where('status', 'approved')->get() as $etudiant) {
        $moyenneEtudiant = $etudiant->moyennes()->avg('moyenne');
        if ($moyenneEtudiant >= 10) {
            $etudiantsReussite++;
        }
    }
    
    $tauxReussite = $etudiants > 0 ? round(($etudiantsReussite / $etudiants) * 100) : 0;
    
    // Nouveaux étudiants ce mois
    $nouveauxEtudiants = User::where('role', 'etudiant')
        ->where('status', 'approved')
        ->whereBetween('created_at', [$moisActuel->startOfMonth(), $moisActuel->endOfMonth()])
        ->count();
    
    return Inertia::render('Conseiller/Statistiques', [
        'stats' => [
            'etudiants' => $etudiants,
            'etudiantsGrowth' => $etudiantsGrowth,
            'filieres' => $filieres,
            'filieresGrowth' => $filieresGrowth,
            'moyenneGenerale' => $moyenneGenerale,
            'moyenneGrowth' => $moyenneGrowth,
            'enAttente' => $enAttente,
            'inscriptionsMois' => $inscriptionsMois,
            'nouveauxEtudiants' => $nouveauxEtudiants,
            'tauxReussite' => $tauxReussite,
            'evolution' => $evolution,
            'filieresRepartition' => [
                'labels' => $filieresRepartition->pluck('label')->toArray(),
                'valeurs' => $filieresRepartition->pluck('valeur')->toArray()
            ],
            'performanceParFiliere' => [
                'labels' => collect($performanceParFiliere)->pluck('label')->toArray(),
                'valeurs' => collect($performanceParFiliere)->pluck('valeur')->toArray()
            ]
        ]
    ]);
}

    // ══════════════════════════════════════════════════════════════════════════
    // ÉVALUATION / QUESTIONS
    // ══════════════════════════════════════════════════════════════════════════

    public function evaluation()
    {
        $questions = Question::withCount('reponses')->orderBy('created_at', 'desc')->get();
        return Inertia::render('Conseiller/Evaluation', ['questions' => $questions]);
    }
public function storeQuestion(\Illuminate\Http\Request $request)
{
    $validated = $request->validate([
        'question'    => 'required|string|min:3',
        'type'        => 'required|in:text,choice,multiple',
        'options'     => 'nullable|array',
        'options.*'   => 'nullable|string',
        'categorie'   => 'nullable|in:interet,competence,preference',
        'mots_cles'   => 'nullable|array',
        'mots_cles.*' => 'nullable|string',
    ]);
 
    $validated['options'] = !empty($validated['options'])
        ? array_values(array_filter($validated['options'], fn($o) => !empty(trim($o))))
        : null;
 
    $validated['mots_cles'] = !empty($validated['mots_cles'])
        ? array_values(array_filter($validated['mots_cles'], fn($m) => !empty(trim($m))))
        : null;
 
    $validated['categorie'] ??= 'interet';
    $validated['is_active']   = true;
 
    \App\Models\Question::create($validated);
 
    return redirect()
        ->route('conseiller.dashboard')
        ->with('success', '✅ Question publiée ! Les étudiants peuvent maintenant y répondre.');
}

 

    public function destroyQuestion($id)
{
    $question = \App\Models\Question::findOrFail($id);
    $question->reponses()->delete();
    $question->delete();
 
    return redirect()
        ->route('conseiller.dashboard')
        ->with('success', 'Question supprimée.');
}

  public function resultats()
{
    $questionnaires = Questionnaire::with(['etudiant', 'questions.reponses'])
        ->where('conseiller_id', Auth::id())
        ->where('statut', 'repondu')
        ->orderBy('repondu_le', 'desc')
        ->get();

    return inertia('Conseiller/Resultats', [
        'questionnaires' => $questionnaires,
    ]);
}
    // ══════════════════════════════════════════════════════════════════════════
    // FILIÈRES
    // ══════════════════════════════════════════════════════════════════════════

    public function filieres()
    {
        return Inertia::render('Conseiller/Filieres', ['filieres' => Filiere::all()]);
    }

    public function storeFiliere(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:filieres,specialite',
            'description' => 'nullable|string',
        ]);

        try {
            $filiere = Filiere::create([
                'specialite' => $request->name,
                'code'       => strtoupper(substr($request->name, 0, 3)) . rand(100, 999),
                'universite' => $request->universite ?? null,
                'type_bac'   => $request->type_bac   ?? null,
                'formule'    => $request->formule     ?? null,
                'annee'      => $request->annee       ?? date('Y'),
            ]);

            return redirect()->back()->with('success', 'Filière ajoutée avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroyFiliere($id)
    {
        $filiere = Filiere::findOrFail($id);
        if (User::where('filiere_id', $id)->count() > 0) {
            return redirect()->back()->with('error', 'Impossible — filière liée à des étudiants');
        }
        $filiere->delete();
        return redirect()->back()->with('success', 'Filière supprimée');
    }
}