<?php
// app/Http/Controllers/QuestionnaireController.php
// ✅ VERSION FINALE

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\Question;
use App\Models\Recommandation;
use App\Models\Reponse;
use App\Models\User;
use App\Services\ProfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionnaireController extends Controller
{
    public function __construct(private ProfilService $profilService) {}

    // ── CONSEILLER ────────────────────────────────────────────────────────────

    public function index()
    {
        $questionnaires = Questionnaire::with(['etudiant:id,name', 'questions'])
            ->where('conseiller_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $etudiants = User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return inertia('Conseiller/Questionnaires/Index', [
            'questionnaires' => $questionnaires,
            'etudiants'      => $etudiants,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre'                   => 'required|string|max:255',
            'questions'               => 'required|array|min:1',
            'questions.*.texte'       => 'required|string|min:2',
            'questions.*.type'        => 'required|in:text,choix_unique,choix_multiple',
            'questions.*.options'     => 'nullable|array',
            'questions.*.categorie'   => 'nullable|in:interet,competence,preference',
            'questions.*.mots_cles'   => 'nullable|array',
            'questions.*.mots_cles.*' => 'nullable|string',
        ]);

        $etudiants = User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->get();

        if ($etudiants->isEmpty()) {
            return redirect()->back()->with('error', 'Aucun étudiant approuvé trouvé.');
        }

        DB::transaction(function () use ($request, $etudiants) {
            foreach ($etudiants as $etudiant) {
                $questionnaire = Questionnaire::create([
                    'conseiller_id' => Auth::id(),
                    'etudiant_id'   => $etudiant->id,
                    'titre'         => $request->titre,
                    'statut'        => 'envoye',
                    'envoye_le'     => now(),
                ]);

                foreach ($request->questions as $index => $q) {
                    $motsClés = !empty($q['mots_cles'])
                        ? array_values(array_filter(array_map('trim', $q['mots_cles'])))
                        : null;

                    Question::create([
                        'questionnaire_id' => $questionnaire->id,
                        'texte'            => $q['texte'],
                        'type'             => $q['type'],
                        'options'          => $q['options'] ?? null,
                        'ordre'            => $index,
                        'categorie'        => $q['categorie'] ?? 'interet',
                        'mots_cles'        => $motsClés,
                    ]);
                }
            }
        });

        return redirect()
            ->route('conseiller.questionnaires.index')
            ->with('success', ' Questionnaire envoyé à ' . $etudiants->count() . ' étudiant(s) !');
    }

    public function destroy(Questionnaire $questionnaire)
    {
        if ($questionnaire->conseiller_id !== Auth::id()) abort(403);
        $questionnaire->delete();
        return redirect()->back()->with('success', 'Questionnaire supprimé.');
    }

    public function resultats(Questionnaire $questionnaire)
    {
        if ($questionnaire->conseiller_id !== Auth::id()) abort(403);
        $questionnaire->load(['etudiant:id,name', 'questions.reponses.etudiant']);
        return inertia('Conseiller/Questionnaires/Resultats', [
            'questionnaire' => $questionnaire,
        ]);
    }

    // ── ÉTUDIANT ──────────────────────────────────────────────────────────────
    public function soumettreReponses(Request $request, Questionnaire $questionnaire)
    {
        if ($questionnaire->etudiant_id !== Auth::id()) abort(403);

        $request->validate([
            'reponses'               => 'required|array|min:1',
            'reponses.*.question_id' => 'required|integer|exists:questions,id',
            'reponses.*.reponse'     => 'required|string|min:1|max:2000',
        ]);

        DB::transaction(function () use ($request, $questionnaire) {
            foreach ($request->reponses as $item) {
                Reponse::updateOrCreate(
                    [
                        'questionnaire_id' => $questionnaire->id,
                        'question_id'      => $item['question_id'],
                        'etudiant_id'      => Auth::id(),
                    ],
                    ['reponse' => trim($item['reponse'])]
                );
            }

            $questionnaire->update([
                'statut'     => 'repondu',
                'repondu_le' => now(),
            ]);
        });

        // Recalcul profil + recommandations (silencieux si tables absentes)
        try {
            $etudiant = Auth::user();
            $this->profilService->buildProfile($etudiant);

            $recoCount = Recommandation::where('etudiant_id', $etudiant->id)->count();
            if ($recoCount === 0) {
                Log::info("ProfilService: 0 recommandation générée pour l'étudiant {$etudiant->id}");
            }
        } catch (\Throwable $e) {
            Log::warning('ProfilService: ' . $e->getMessage());
        }

        // ✅ Redirect Inertia → flash géré automatiquement dans $page.props.flash
        return redirect()
            ->route('etudiant.dashboard')
            ->with('success', '✅ Réponses envoyées ! Vos recommandations ont été mises à jour.');
    }
}