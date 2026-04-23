<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; 
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

    public function statistiques()
{
    $now         = \Carbon\Carbon::now();
    $moisDernier = $now->copy()->subMonth();
 
    // ── 1. KPI ────────────────────────────────────────────────────────────────
    $totalEtudiants   = \App\Models\User::where('role', 'etudiant')->where('status', 'approved')->count();
    $totalConseillers = \App\Models\User::where('role', 'conseiller')->where('status', 'approved')->count();
    $totalPending     = \App\Models\User::where('status', 'pending')->count();
    $pendingEtudiants = \App\Models\User::where('role', 'etudiant')->where('status', 'pending')->count();
    $pendingConseillers = \App\Models\User::where('role', 'conseiller')->where('status', 'pending')->count();
 
    // Croissances vs mois précédent
    $growth = function (string $role) use ($now, $moisDernier): float {
        $prev = \App\Models\User::where('role', $role)->where('status', 'approved')
            ->whereBetween('created_at', [$moisDernier->copy()->startOfMonth(), $moisDernier->copy()->endOfMonth()])
            ->count();
        $curr = \App\Models\User::where('role', $role)->where('status', 'approved')
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();
        return $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : ($curr > 0 ? 100 : 0);
    };
 
    $etudiantsGrowth   = $growth('etudiant');
    $conseillersGrowth = $growth('conseiller');
 
    // ── 2. Croissance utilisateurs par mois — Line chart (12 derniers mois) ───
    $croissanceMensuelle = [];
    for ($i = 11; $i >= 0; $i--) {
        $mois  = $now->copy()->subMonths($i);
        $debut = $mois->copy()->startOfMonth();
        $fin   = $mois->copy()->endOfMonth();
 
        $croissanceMensuelle[] = [
            'label'       => $mois->format('M Y'),
            'etudiants'   => \App\Models\User::where('role', 'etudiant')
                ->where('status', 'approved')
                ->whereBetween('created_at', [$debut, $fin])
                ->count(),
            'conseillers' => \App\Models\User::where('role', 'conseiller')
                ->where('status', 'approved')
                ->whereBetween('created_at', [$debut, $fin])
                ->count(),
        ];
    }
 
    // ── 3. Répartition utilisateurs — Pie ─────────────────────────────────────
    $totalApproved = $totalEtudiants + $totalConseillers
        + \App\Models\User::where('role', 'admin')->where('status', 'approved')->count();
 
    // ── 4. Comptes en attente par rôle — Column ───────────────────────────────
    $pendingParRole = [
        ['role' => 'Étudiants',   'nb' => $pendingEtudiants],
        ['role' => 'Conseillers', 'nb' => $pendingConseillers],
    ];
 
    // ── 5. Inscriptions par semaine (8 dernières semaines) — Area ─────────────
    $inscriptionsHebdo = [];
    for ($i = 7; $i >= 0; $i--) {
        $semaine = $now->copy()->subWeeks($i);
        $debut   = $semaine->copy()->startOfWeek();
        $fin     = $semaine->copy()->endOfWeek();
 
        $inscriptionsHebdo[] = [
            'label'       => 'S' . $semaine->weekOfYear,
            'etudiants'   => \App\Models\User::where('role', 'etudiant')
                ->whereBetween('created_at', [$debut, $fin])->count(),
            'conseillers' => \App\Models\User::where('role', 'conseiller')
                ->whereBetween('created_at', [$debut, $fin])->count(),
        ];
    }
 
    // ── 6. Top filières (par nombre d'étudiants inscrits) — Horizontal Bar ────
    $topFilieres = \App\Models\Filiere::withCount(['etudiants' => fn($q) =>
            $q->where('status', 'approved')])
        ->orderByDesc('etudiants_count')
        ->limit(8)
        ->get()
        ->map(fn($f) => [
            'nom' => $f->specialite ?? $f->nom ?? '—',
            'nb'  => $f->etudiants_count,
        ]);
 
    // ── 7. Activité chat — Column-Line (30 derniers jours) ────────────────────
    $activiteChat = [];
    for ($i = 29; $i >= 0; $i -= 3) {
        $jour  = $now->copy()->subDays($i);
        $debut = $jour->copy()->startOfDay();
        $fin   = $jour->copy()->addDays(2)->endOfDay();
 
        $activiteChat[] = [
            'label'          => $jour->format('d/m'),
            'conversations'  => \App\Models\Conversation::whereBetween('created_at', [$debut, $fin])->count(),
            'messages'       => \App\Models\Message::whereBetween('created_at', [$debut, $fin])->count(),
        ];
    }
 
    return Inertia::render('SuperAdmin/Statistiques', [
        'stats' => [
            // KPI
            'totalEtudiants'     => $totalEtudiants,
            'totalConseillers'   => $totalConseillers,
            'totalPending'       => $totalPending,
            'etudiantsGrowth'    => $etudiantsGrowth,
            'conseillersGrowth'  => $conseillersGrowth,
 
            // Pie — répartition utilisateurs
            'pieUtilisateurs' => [
                'labels' => ['Étudiants', 'Conseillers', 'Admins'],
                'valeurs' => [
                    $totalEtudiants,
                    $totalConseillers,
                    max(0, $totalApproved - $totalEtudiants - $totalConseillers),
                ],
            ],
 
            // Column — pending par rôle
            'pendingParRole' => [
                'labels' => collect($pendingParRole)->pluck('role')->toArray(),
                'valeurs' => collect($pendingParRole)->pluck('nb')->toArray(),
            ],
 
            // Line — croissance mensuelle (12 mois)
            'croissanceMensuelle' => [
                'labels'      => collect($croissanceMensuelle)->pluck('label')->toArray(),
                'etudiants'   => collect($croissanceMensuelle)->pluck('etudiants')->toArray(),
                'conseillers' => collect($croissanceMensuelle)->pluck('conseillers')->toArray(),
            ],
 
            // Area — inscriptions hebdo
            'inscriptionsHebdo' => [
                'labels'      => collect($inscriptionsHebdo)->pluck('label')->toArray(),
                'etudiants'   => collect($inscriptionsHebdo)->pluck('etudiants')->toArray(),
                'conseillers' => collect($inscriptionsHebdo)->pluck('conseillers')->toArray(),
            ],
 
            // Bar horizontal — top filières
            'topFilieres' => [
                'labels' => $topFilieres->pluck('nom')->toArray(),
                'valeurs' => $topFilieres->pluck('nb')->toArray(),
            ],
 
            // Column-Line — activité chat
            'activiteChat' => [
                'labels'        => collect($activiteChat)->pluck('label')->toArray(),
                'conversations' => collect($activiteChat)->pluck('conversations')->toArray(),
                'messages'      => collect($activiteChat)->pluck('messages')->toArray(),
            ],
        ],
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