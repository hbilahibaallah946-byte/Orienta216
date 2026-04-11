<?php
namespace App\Http\Controllers;

use App\Models\Recommandation;
use App\Models\User;
use App\Services\ProfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommandationController extends Controller
{
    public function __construct(private ProfilService $profilService) {}

    /** GET /api/profil/moi */
    public function moi()
    {
        return response()->json(
            $this->profilService->getProfilComplet(Auth::user())
        );
    }

    /** POST /api/profil/recalculer */
    public function recalculer()
    {
        $etudiant = Auth::user();
        $this->profilService->buildProfile($etudiant);
        return response()->json([
            'success' => true,
            ...$this->profilService->getProfilComplet($etudiant),
        ]);
    }

    /** GET /api/profil/etudiant/{id} — pour le conseiller */
    public function etudiant(int $id)
    {
        $etudiant = User::where('id', $id)
            ->where('role', 'etudiant')
            ->firstOrFail();

        $data = $this->profilService->getProfilComplet($etudiant);

        $data['etudiant']     = ['id' => $etudiant->id, 'name' => $etudiant->name];
        $data['message_auto'] = $this->genererMessageAuto($data);

        return response()->json($data);
    }

    private function genererMessageAuto(array $data): string
    {
        $recos  = $data['recommandations'] ?? [];
        $profil = $data['profil']          ?? null;

        if (empty($recos)) {
            return "Bonjour 👋 Cet étudiant n'a pas encore rempli le questionnaire.";
        }

        $msg = "Bonjour 👋 Selon le questionnaire, voici mes suggestions :\n\n";
        foreach ($recos as $i => $r) {
            $e    = match ($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => '•' };
            $msg .= "{$e} {$r['filiere']['nom']} ({$r['score']}%)\n";
        }
        $msg .= "\n";

        $top   = $recos[0];
        $score = $top['score'];
        $nom   = $top['filiere']['nom'];

        if ($score >= 80)      $msg .= "✅ {$nom} correspond très bien à ton profil !";
        elseif ($score >= 60)  $msg .= "👍 {$nom} est une bonne option, à explorer ensemble.";
        else                   $msg .= "💬 Les résultats sont mitigés — parlons-en ensemble.";

        if ($profil && !empty($profil['interets'])) {
            $msg .= "\n\n🎯 Intérêts détectés : " . implode(', ', array_slice($profil['interets'], 0, 3));
        }

        $msg .= "\n\nN'hésite pas à me poser tes questions 😊";
        return $msg;
    }
}