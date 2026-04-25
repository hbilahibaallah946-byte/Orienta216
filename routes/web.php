<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MoyenneController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Admin\FiliereController;
use App\Models\Filiere;
use App\Http\Controllers\Admin\EvaluationController;
use App\Http\Controllers\ConseillerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\UserValidationController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ReponseController;
use App\Http\Controllers\QuestionController;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChatController;
use App\Models\Recommandation;
use App\Http\Controllers\RecommandationController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\UniversityPdfController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Page d'accueil
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Rafraîchit explicitement le token CSRF (utile pour le chat AJAX après expiration session)
Route::get('/csrf-token', function (Request $request) {
    $request->session()->regenerateToken();
    return response()->json(['token' => csrf_token()]);
})->middleware('web');

/*
|--------------------------------------------------------------------------
| Routes pour tous les utilisateurs authentifiés
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/save', [ProfileController::class, 'save'])->name('profile.save');
    Route::get('/profile/load', [ProfileController::class, 'load'])->name('profile.load');

    // Moyennes
    Route::post('/moyennes', [MoyenneController::class, 'store'])->name('moyennes.store');
    Route::get('/moyennes', [MoyenneController::class, 'index'])->name('moyennes.index');

    // Password
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // Language
    Route::put('/language', [LanguageController::class, 'update'])->name('language.update');
    Route::get('/language/current', [LanguageController::class, 'current'])->name('language.current');

    // Account
    Route::delete('/account', [AccountController::class, 'destroy'])->name('account.destroy');

    // Chat API
    Route::get('/api/chat/messages/{conversationId}', [ChatController::class, 'getMessages']);
    Route::post('/api/chat/etudiant/send', [ChatController::class, 'sendMessageEtudiant']);
    Route::get('/api/chat/etudiant/conversation', [ChatController::class, 'getMaConversation']);
    Route::get('/api/chat/conseiller/conversations', [ChatController::class, 'getAllConversations']);
    Route::post('/api/chat/conseiller/prendre', [ChatController::class, 'prendreConversation']);
    Route::post('/api/chat/conseiller/send', [ChatController::class, 'sendMessageConseiller']);
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/api/chat/marquer-lus/{conversationId}', [ChatController::class, 'markMessagesAsRead']);
    Route::get('/api/chat/unread-count', [ChatController::class, 'getUnreadCount']);
    Route::get('/api/chat/check/{conversationId}', [ChatController::class, 'checkConversationStatus']);

    // Profil & Recommandations
    Route::get('/api/profil/moi', [RecommandationController::class, 'moi']);
    Route::post('/api/profil/recalculer', [RecommandationController::class, 'recalculer']);
    Route::post('/api/profil/recalculer-etudiant/{id}', [RecommandationController::class, 'recalculerEtudiant']);
    Route::get('/api/profil/etudiant/{id}', [RecommandationController::class, 'etudiant']);
// Comment Wall API
Route::get('/api/comments', [App\Http\Controllers\CommentController::class, 'index']);
Route::post('/api/comments', [App\Http\Controllers\CommentController::class, 'store']);
Route::delete('/api/comments/{id}', [App\Http\Controllers\CommentController::class, 'destroy']);
Route::post('/api/comments/{id}/react', [App\Http\Controllers\CommentController::class, 'react']);
Route::post('/api/comments/{id}/report', [App\Http\Controllers\CommentController::class, 'report']);
});

/*
|--------------------------------------------------------------------------
| Routes ADMIN (Super Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/pending-requests', [UserValidationController::class, 'pendingRequests'])->name('pending-requests');
    Route::post('/approve/{id}', [UserValidationController::class, 'approve'])->name('approve');
    Route::post('/reject/{id}', [UserValidationController::class, 'reject'])->name('reject');
    Route::get('/users', [UserValidationController::class, 'approvedUsers'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserValidationController::class, 'destroy'])->name('users.destroy');
    Route::get('/filieres', [FiliereController::class, 'index'])->name('filieres.index');
    Route::post('/filieres', [FiliereController::class, 'store'])->name('filieres.store');
    Route::delete('/filieres/{id}', [FiliereController::class, 'destroy'])->name('filieres.destroy');
    Route::put('/filieres/{filiere}', [FiliereController::class, 'update'])->name('filieres.update');
    Route::get('/statistiques', [AdminController::class, 'statistiques'])->name('statistiques');
});

/*
|--------------------------------------------------------------------------
| Routes CONSEILLER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:conseiller'])->prefix('conseiller')->name('conseiller.')->group(function () {

    Route::get('/dashboard', [ConseillerController::class, 'dashboard'])->name('dashboard');
    Route::get('/etudiants', [ConseillerController::class, 'etudiants'])->name('etudiants.index');
    Route::get('/etudiants/create', [ConseillerController::class, 'createEtudiant'])->name('etudiants.create');
    Route::post('/etudiants', [ConseillerController::class, 'storeEtudiant'])->name('etudiants.store');
    Route::get('/statistiques', [ConseillerController::class, 'statistiques'])->name('statistiques');
    Route::get('/etudiants/{id}', [ConseillerController::class, 'showEtudiant'])->name('etudiants.show');
    Route::get('/filieres', [ConseillerController::class, 'filieres'])->name('filieres.index');
    Route::post('/filieres', [ConseillerController::class, 'storeFiliere'])->name('filieres.store');
    Route::put('/filieres/{filiere}', [ConseillerController::class, 'updateFiliere'])->name('filieres.update');
    Route::delete('/filieres/{id}', [ConseillerController::class, 'destroyFiliere'])->name('filieres.destroy');
    Route::post('/filieres/import-pdf', [ConseillerController::class, 'importFilieresFromPdf'])->name('filieres.import-pdf');
    Route::post('/filieres/import-csv', [ConseillerController::class, 'importFilieresFromCsv'])->name('filieres.import-csv');
    Route::get('/questionnaires', [QuestionnaireController::class, 'index'])->name('questionnaires.index');
    Route::post('/questionnaires', [QuestionnaireController::class, 'store'])->name('questionnaires.store');
    Route::get('/questionnaires/{questionnaire}/resultats', [QuestionnaireController::class, 'resultats'])->name('questionnaires.resultats');
    Route::delete('/questionnaires/{questionnaire}', [QuestionnaireController::class, 'destroy'])->name('questionnaires.destroy');

    // Gestion du PDF des universités privées
    Route::get('/university-pdf', [UniversityPdfController::class, 'manage'])
        ->name('university-pdf.manage');
    Route::post('/university-pdf/upload', [UniversityPdfController::class, 'upload'])
        ->name('university-pdf.upload');
    Route::delete('/university-pdf', [UniversityPdfController::class, 'delete'])
        ->name('university-pdf.delete');
});

/*
|--------------------------------------------------------------------------
| Routes ETUDIANT
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:etudiant'])->prefix('etudiant')->name('etudiant.')->group(function () {

    Route::get('/dashboard', function () {
    $questionnaires = \App\Models\Questionnaire::with(['conseiller:id,name', 'questions'])
        ->where('etudiant_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->get();

    return Inertia::render('Etudiant/Dashboard', [
        'filieres' => Filiere::all(),
        'moyennes' => [],
        'user' => Auth::user(),
        'questionnaires' => $questionnaires, // ← ajouté
        'flash' => [
            'success' => session('success'),
            'error' => session('error'),
        ],
    ]);
})->name('dashboard');

    Route::post('/questionnaires/{questionnaire}/reponses', [QuestionnaireController::class, 'soumettreReponses'])->name('questionnaires.soumettre');
    Route::get('/notes', [MoyenneController::class, 'index'])->name('notes');
    Route::get('/moyennes', [MoyenneController::class, 'index'])->name('moyennes.index');
    Route::get('/calcul-moyennes', function () {
        return inertia('Etudiant/CalculMoyennes');
    })->name('calcul-moyennes');

    // Consultation du PDF des universités privées
    Route::get('/private-universities', [UniversityPdfController::class, 'show'])
        ->name('private-universities');
});

/*
|--------------------------------------------------------------------------
| Redirections Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard-redirect', function () {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'conseiller' => redirect()->route('conseiller.dashboard'),
            default => redirect()->route('etudiant.dashboard'),
        };
    })->name('dashboard.redirect');

    Route::get('/dashboard', function () {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'conseiller' => redirect()->route('conseiller.dashboard'),
            default => redirect()->route('etudiant.dashboard'),
        };
    })->name('dashboard');
});

Route::get('/register/pending', function () {
    return Inertia::render('Auth/Pending');
})->name('register.pending');

// Inclusion des routes d'authentification Laravel Breeze/Jetstream
require __DIR__.'/auth.php';