<?php

namespace App\Http\Controllers;

use App\Models\EtudiantProfile;
use App\Models\Moyenne;
use App\Services\ProfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MoyenneController extends Controller
{
    public function __construct(private ProfilService $profilService) {}
    public function store(Request $request)
    {
        try {
            // Vérifier l'authentification
            if (!Auth::check()) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            // Log des données reçues pour déboguer
            Log::info('Données reçues:', $request->all());
            
            // Valider les données
            $validated = $request->validate([
                'specialite' => 'required|string',
                'matieres' => 'required|array',
                'moyenne' => 'required|numeric',
                'score' => 'required|numeric',
                'score_plus_7' => 'required|numeric'
            ]);
            
            // Créer la moyenne
            $moyenne = Moyenne::create([
                'user_id' => Auth::id(),
                'specialite' => $validated['specialite'],
                'matieres' => $validated['matieres'], // Laravel convertira automatiquement en JSON
                'session' => now()->format('Y'), // Ajouter la session actuelle si nécessaire
                'moyenne' => $validated['moyenne'],
                'score' => $validated['score'],
                'score_plus_7' => $validated['score_plus_7']
            ]);

            if (EtudiantProfile::where('etudiant_id', Auth::id())->exists()) {
                $this->profilService->computeRecommandations(Auth::user());
            }

            return response()->json($moyenne, 201);
            
        } catch (\Exception $e) {
            // Logger l'erreur détaillée
            Log::error('Erreur lors de l\'enregistrement de la moyenne: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Erreur lors de l\'enregistrement',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function index()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }
            
            $moyennes = Moyenne::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json($moyennes);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des moyennes: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Erreur lors du chargement',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}