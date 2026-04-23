<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Conversation;
use App\Models\Recommandation;

class ConseillerController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        return Inertia::render('Conseiller/Dashboard', [
            'totalEtudiants' => User::where('role', 'etudiant')
                ->where('status', 'approved')
                ->count(),
            'totalFilieres' => Filiere::count(),
            'questionnairesCount' => Questionnaire::where('conseiller_id', $user->id)->count(),
            'recentEtudiants' => User::where('role', 'etudiant')
                ->where('status', 'approved')
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'email', 'created_at']),
            'recentQuestionnaires' => Questionnaire::with('etudiant')
                ->where('conseiller_id', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
            'conseiller' => $user,
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
        $conseiller = Auth::user();
        $now        = Carbon::now();

        // ── 1. Cartes KPI ─────────────────────────────────────────────────────────
        $totalEtudiants = User::where('role', 'etudiant')
            ->where('status', 'approved')
            ->count();

        $mesConversations = Conversation::where('conseiller_id', $conseiller->id)
            ->where('statut', 'pris_en_charge')
            ->count();

        $questionnairesRemplis = Questionnaire::where('conseiller_id', $conseiller->id)
            ->where('statut', 'repondu')
            ->count();

        $totalQuestionnaires = Questionnaire::where('conseiller_id', $conseiller->id)
            ->count();

        $tauxReponse = $totalQuestionnaires > 0
            ? round(($questionnairesRemplis / $totalQuestionnaires) * 100)
            : 0;

        // ── 2. Filières les plus recommandées (Pie / Bar) ─────────────────────────
        $filieresTrending = Recommandation::with('filiere:id,specialite')
            ->where('rang', 1)
            ->selectRaw('filiere_id, COUNT(*) as nb, ROUND(AVG(score)) as score_moyen')
            ->groupBy('filiere_id')
            ->orderByDesc('nb')
            ->limit(6)
            ->get()
            ->map(fn($r) => [
                'nom'         => $r->filiere->specialite ?? '—',
                'nb'          => $r->nb,
                'score_moyen' => $r->score_moyen,
            ]);

        // ── 3. Évolution conversations (8 dernières semaines) — Area chart ────────
        $evolutionConvs = [];
        for ($i = 7; $i >= 0; $i--) {
            $semaine = $now->copy()->subWeeks($i);
            $debut   = $semaine->copy()->startOfWeek();
            $fin     = $semaine->copy()->endOfWeek();

            $evolutionConvs[] = [
                'label' => 'S' . $semaine->weekOfYear,
                'prises' => Conversation::where('conseiller_id', $conseiller->id)
                    ->where('statut', 'pris_en_charge')
                    ->whereBetween('pris_le', [$debut, $fin])
                    ->count(),
                'en_attente' => Conversation::where('statut', 'en_attente')
                    ->whereBetween('created_at', [$debut, $fin])
                    ->count(),
            ];
        }

        // ── 4. Répartition des étudiants par questionnaire rempli vs non rempli — Pie ─
        $etudiantsAvecReponses = Questionnaire::where('conseiller_id', $conseiller->id)
            ->where('statut', 'repondu')
            ->distinct('etudiant_id')
            ->count('etudiant_id');

        $etudiantsSansReponses = max(0, $totalEtudiants - $etudiantsAvecReponses);

        // ── 5. Scores moyens par filière recommandée — Column chart ───────────────
        $scoresParFiliere = Recommandation::with('filiere:id,specialite')
            ->selectRaw('filiere_id, ROUND(AVG(score)) as score_moyen, COUNT(*) as nb_etudiants')
            ->groupBy('filiere_id')
            ->orderByDesc('score_moyen')
            ->limit(8)
            ->get()
            ->map(fn($r) => [
                'nom'          => $r->filiere->specialite ?? '—',
                'score_moyen'  => (int) $r->score_moyen,
                'nb_etudiants' => $r->nb_etudiants,
            ]);

        // ── 6. Activité des 7 derniers jours (Column-Line) ───────────────────────
        $activite7Jours = [];
        for ($i = 6; $i >= 0; $i--) {
            $jour   = $now->copy()->subDays($i);
            $debut  = $jour->copy()->startOfDay();
            $fin    = $jour->copy()->endOfDay();

            $activite7Jours[] = [
                'label'    => $jour->format('D'),
                'messages' => \App\Models\Message::whereHas('conversation', fn($q) =>
                        $q->where('conseiller_id', $conseiller->id))
                    ->where('sender_id', $conseiller->id)
                    ->whereBetween('created_at', [$debut, $fin])
                    ->count(),
                'questionnaires' => Questionnaire::where('conseiller_id', $conseiller->id)
                    ->where('statut', 'repondu')
                    ->whereBetween('repondu_le', [$debut, $fin])
                    ->count(),
            ];
        }

        return Inertia::render('Conseiller/Statistiques', [
            'stats' => [
                'totalEtudiants'        => $totalEtudiants,
                'mesConversations'      => $mesConversations,
                'questionnairesRemplis' => $questionnairesRemplis,
                'totalQuestionnaires'   => $totalQuestionnaires,
                'tauxReponse'           => $tauxReponse,

                'pieQuestionnaires' => [
                    'labels' => ['Ont répondu', 'N\'ont pas répondu'],
                    'valeurs' => [$etudiantsAvecReponses, $etudiantsSansReponses],
                ],

                'filieresTrending' => $filieresTrending,

                'scoresParFiliere' => [
                    'labels' => $scoresParFiliere->pluck('nom')->toArray(),
                    'scores' => $scoresParFiliere->pluck('score_moyen')->toArray(),
                    'nb'     => $scoresParFiliere->pluck('nb_etudiants')->toArray(),
                ],

                'evolutionConvs' => [
                    'labels'     => collect($evolutionConvs)->pluck('label')->toArray(),
                    'prises'     => collect($evolutionConvs)->pluck('prises')->toArray(),
                    'en_attente' => collect($evolutionConvs)->pluck('en_attente')->toArray(),
                ],

                'activite7Jours' => [
                    'labels'         => collect($activite7Jours)->pluck('label')->toArray(),
                    'messages'       => collect($activite7Jours)->pluck('messages')->toArray(),
                    'questionnaires' => collect($activite7Jours)->pluck('questionnaires')->toArray(),
                ],
            ],
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

    public function storeQuestion(Request $request)
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

        Question::create($validated);

        return redirect()
            ->route('conseiller.dashboard')
            ->with('success', '✅ Question publiée ! Les étudiants peuvent maintenant y répondre.');
    }

    public function destroyQuestion($id)
    {
        $question = Question::findOrFail($id);
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
            'licence'     => 'nullable|string|max:255',
            'code'        => 'nullable|string|max:50',
            'universite'  => 'nullable|string|max:255',
            'type_bac'    => 'nullable|string|max:255',
            'formule'     => 'nullable|string|max:255',
        ]);

        try {
            $filiere = Filiere::create([
                'specialite' => $request->name,
                'licence'    => $request->licence ?? $request->name,
                'code'       => $request->code ?: strtoupper(substr($request->name, 0, 3)) . rand(100, 999),
                'universite' => $request->universite ?? null,
                'type_bac'   => $request->type_bac   ?? null,
                'formule'    => $request->formule     ?? null,
            ]);

            return redirect()->back()->with('success', 'Filière ajoutée avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function updateFiliere(Request $request, Filiere $filiere)
    {
        $request->validate([
            'name'       => 'required|string|max:255|unique:filieres,specialite,' . $filiere->id,
            'licence'    => 'nullable|string|max:255',
            'code'       => 'nullable|string|max:50',
            'universite' => 'nullable|string|max:255',
            'type_bac'   => 'nullable|string|max:255',
            'formule'    => 'nullable|string|max:255',
        ]);

        $filiere->update([
            'specialite' => $request->name,
            'licence'    => $request->licence,
            'code'       => $request->code ?: $filiere->code,
            'universite' => $request->universite,
            'type_bac'   => $request->type_bac,
            'formule'    => $request->formule,
        ]);

        return redirect()->back()->with('success', 'Filière mise à jour avec succès');
    }

    public function importFilieresFromPdf(Request $request)
    {
        $request->validate([
            'pdf_path' => ['nullable', 'string'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf'],
        ]);

        $path = trim((string) $request->input('pdf_path', ''));

        if ($request->hasFile('pdf_file')) {
            $stored = $request->file('pdf_file')->store('imports', 'local');
            $path   = Storage::disk('local')->path($stored);
        }

        if ($path === '') {
            return back()->with('error', 'Veuillez fournir un chemin PDF ou téléverser un fichier PDF.');
        }

        try {
            Artisan::call('filieres:replace-from-pdf', [
                'path' => $path,
            ]);

            $output = trim(Artisan::output());
            return back()->with('success', "Import PDF terminé. {$output}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur import PDF: ' . $e->getMessage());
        }
    }

    public function importFilieresFromCsv(Request $request)
    {
        $request->validate([
            'csv_path' => ['nullable', 'string'],
            'csv_file' => ['nullable', 'file', 'mimes:csv,txt'],
        ]);

        $path = trim((string) $request->input('csv_path', ''));
        if ($request->hasFile('csv_file')) {
            $stored = $request->file('csv_file')->store('imports', 'local');
            $path = Storage::disk('local')->path($stored);
        }

        if ($path === '') {
            return back()->with('error', 'Veuillez fournir un chemin CSV ou téléverser un fichier CSV.');
        }

        try {
            Artisan::call('filieres:replace-from-csv', ['path' => $path]);
            $output = trim(Artisan::output());
            return back()->with('success', "Import CSV terminé. {$output}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur import CSV: ' . $e->getMessage());
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