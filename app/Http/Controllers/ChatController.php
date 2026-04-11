<?php
// app/Http/Controllers/ChatController.php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\ProfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ChatController extends Controller
{
    public function __construct(private ProfilService $profilService) {}

    /**
     * POST /api/chat/etudiant/send
     */
    public function sendMessageEtudiant(Request $request)
    {
        try {
            $request->validate(['message' => 'required|string|max:1000']);

            $etudiant = Auth::user();
            
            if (!$etudiant) {
                return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
            }

            // Récupérer ou créer la conversation
            $conversation = Conversation::firstOrCreate(
                ['etudiant_id' => $etudiant->id],
                [
                    'etudiant_id' => $etudiant->id,
                    'statut' => 'en_attente',
                    'dernier_message_at' => now(),
                ]
            );

            // ✅ CORRECTION : receiver_id peut être NULL
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $etudiant->id,
                'receiver_id' => $conversation->conseiller_id ?? null, // ← NULL autorisé
                'message' => $request->message,
                'is_read' => false,
            ]);

            $conversation->update(['dernier_message_at' => now()]);

            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id,
                'statut' => $conversation->statut,
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'message' => $message->message,
                    'created_at' => $message->created_at,
                    'is_read' => $message->is_read,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur sendMessageEtudiant: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/chat/etudiant/conversation
     */
    public function getMaConversation()
    {
        try {
            $etudiant = Auth::user();
            
            if (!$etudiant) {
                return response()->json(['conversation' => null, 'messages' => []]);
            }

            $conversation = Conversation::where('etudiant_id', $etudiant->id)
                ->whereIn('statut', ['en_attente', 'pris_en_charge'])
                ->first();

            if (!$conversation) {
                return response()->json(['conversation' => null, 'messages' => []]);
            }

            // Marquer les messages du conseiller comme lus
            Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $etudiant->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            $messages = Message::where('conversation_id', $conversation->id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($m) => [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'message' => $m->message,
                    'is_read' => $m->is_read,
                    'created_at' => $m->created_at,
                ]);

            return response()->json([
                'conversation' => [
                    'id' => $conversation->id,
                    'statut' => $conversation->statut,
                    'conseiller_nom' => $conversation->statut === 'pris_en_charge' 
                        ? 'Support Orientation' 
                        : null,
                ],
                'messages' => $messages,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur getMaConversation: ' . $e->getMessage());
            return response()->json(['conversation' => null, 'messages' => []], 500);
        }
    }

   


    // ══════════════════════════════════════════════════════════════════════════
    // CONSEILLER — Voir toutes les conversations
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /api/chat/conseiller/conversations
     * Retourne :
     *   - en_attente : conversations sans conseiller assigné
     *   - mes_conversations : conversations que j'ai prises
     *   - prises_par_autres : conversations prises par un autre conseiller
     */
public function getAllConversations()
{
    $conseillerId = Auth::id();

    // Conversations en attente
    $enAttente = Conversation::with('etudiant:id,name')
        ->where('statut', 'en_attente')
        ->whereHas('messages')
        ->orderByDesc('dernier_message_at')
        ->get()
        ->map(fn($c) => $this->formatConv($c, $conseillerId, 'en_attente'));

    // Mes conversations
    $mesConversations = Conversation::with('etudiant:id,name')
        ->where('statut', 'pris_en_charge')
        ->where('conseiller_id', $conseillerId)
        ->orderByDesc('dernier_message_at')
        ->get()
        ->map(fn($c) => $this->formatConv($c, $conseillerId, 'mes'));

    // Prises par d'autres
    $prisesParAutres = Conversation::with('etudiant:id,name')
        ->where('statut', 'pris_en_charge')
        ->where('conseiller_id', '!=', $conseillerId)
        ->orderByDesc('dernier_message_at')
        ->get()
        ->map(fn($c) => $this->formatConv($c, $conseillerId, 'autres'));

    return response()->json([
        'en_attente'        => $enAttente,
        'mes_conversations' => $mesConversations,
        'prises_par_autres' => $prisesParAutres,
    ]);
}

    /**
     * POST /api/chat/conseiller/prendre
     * Le premier conseiller à appeler cette route "prend" la conversation.
     * Les autres reçoivent un 409 avec le nom du conseiller qui a pris.
     */
    // app/Http/Controllers/ChatController.php


    //chattt
    // Ajoutez cette méthode pour vérifier si une conversation est toujours valide
public function checkConversationStatus($conversationId)
{
    $conversation = Conversation::find($conversationId);
    
    if (!$conversation) {
        return response()->json(['exists' => false]);
    }
    
    return response()->json([
        'exists' => true,
        'statut' => $conversation->statut,
        'conseiller_id' => $conversation->conseiller_id
    ]);
}

// app/Http/Controllers/ChatController.php

public function prendreConversation(Request $request)
{
    try {
        $request->validate(['conversation_id' => 'required|exists:conversations,id']);

        $conseiller = Auth::user();
        $conversation = Conversation::findOrFail($request->conversation_id);

        // Vérifier si déjà prise
        if ($conversation->statut !== 'en_attente') {
            $prisPar = $conversation->pris_par ?? 'un autre conseiller';
            
            return response()->json([
                'success' => false,
                'pris_par' => $prisPar,
                'message' => "Cette conversation a déjà été prise par {$prisPar}"
            ], 409);
        }

        // Prendre la conversation
        $conversation->conseiller_id = $conseiller->id;
        $conversation->pris_par = $conseiller->name;
        $conversation->statut = 'pris_en_charge';
        $conversation->pris_le = now();
        $conversation->save();

        // Mettre à jour les messages existants
        Message::where('conversation_id', $conversation->id)
            ->whereNull('receiver_id')
            ->update(['receiver_id' => $conseiller->id]);

        // Recharger avec les relations
        $conversation->load('etudiant');

        return response()->json([
            'success' => true,
            'message' => "Conversation prise en charge avec succès",
            'conversation' => [
                'id' => $conversation->id,
                'statut' => $conversation->statut,
                'pris_par' => $conversation->pris_par,
                'etudiant_nom' => $conversation->etudiant->name ?? 'Étudiant',
                'etudiant_id' => $conversation->etudiant_id
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * POST /api/chat/conseiller/send
     * Envoie un message dans une conversation dont le conseiller est propriétaire.
     */
    public function sendMessageConseiller(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message'         => 'required|string|max:1000',
        ]);

        $conseiller   = Auth::user();
        $conversation = Conversation::findOrFail($request->conversation_id);

        if ($conversation->conseiller_id !== $conseiller->id) {
            return response()->json([
                'success' => false,
                'message' => "Cette conversation est prise par {$conversation->conseiller_nom}",
            ], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $conseiller->id,
            'receiver_id'     => $conversation->etudiant_id,
            'message'         => $request->message,
            'is_read'         => false,
        ]);

        $conversation->update(['dernier_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => $this->formatMsg($message),
        ]);
    }

    /**
     * GET /api/chat/messages/{conversationId}
     * Récupère les messages d'une conversation + profil étudiant (si conseiller).
     */
    public function getMessages($conversationId)
{
    $user         = Auth::user();
    $conversation = Conversation::findOrFail($conversationId);

    // Vérification des droits
    if ($user->role === 'etudiant' && $conversation->etudiant_id !== $user->id) {
        return response()->json(['success' => false], 403);
    }

    // Marquer comme lus
    if ($user->role === 'conseiller' && $conversation->conseiller_id === $user->id) {
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', $conversation->etudiant_id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    $messages = Message::where('conversation_id', $conversationId)
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(fn($m) => $this->formatMsg($m));

    // Profil étudiant pour le conseiller
    $profil = null;
    if ($user->role === 'conseiller') {
        try {
            $etudiant = User::find($conversation->etudiant_id);
            if ($etudiant) {
                $profil = $this->profilService->getProfilComplet($etudiant);
                // ✅ Utiliser pris_par dans le message auto si besoin
                $profil['message_auto'] = $this->profilService->genererMessageAuto($etudiant);
            }
        } catch (\Throwable $e) {
            // Silencieux
        }
    }

    return response()->json([
        'messages'     => $messages,
        'conversation' => [
            'id'             => $conversation->id,
            'statut'         => $conversation->statut,
            'conseiller_id'  => $conversation->conseiller_id,
            'pris_par'       => $conversation->pris_par,  // ← Changement ici
            'etudiant_id'    => $conversation->etudiant_id,
        ],
        'profil' => $profil,
    ]);
}

    // ══════════════════════════════════════════════════════════════════════════
    // COMMUN
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /api/chat/unread-count
     */
    public function getUnreadCount()
    {
        $user = Auth::user();

        if ($user->role === 'conseiller') {
            // Messages non lus dans mes conversations + nouvelles en attente
            $unreadMine = Message::whereHas('conversation', fn($q) =>
                    $q->where('conseiller_id', $user->id))
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();

            $newWaiting = Conversation::where('statut', 'en_attente')
                ->whereHas('messages')
                ->count();

            $count = $unreadMine + $newWaiting;
        } else {
            // Étudiant : messages non lus du conseiller
            $conversation = Conversation::where('etudiant_id', $user->id)
                ->where('statut', 'pris_en_charge')
                ->first();

            $count = $conversation
                ? Message::where('conversation_id', $conversation->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->count()
                : 0;
        }

        return response()->json(['unread_count' => $count]);
    }

    /**
     * POST /api/chat/marquer-lus/{conversationId}
     */
    public function markMessagesAsRead($conversationId)
    {
        $user         = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        if ($user->role === 'conseiller' && $conversation->conseiller_id === $user->id) {
            Message::where('conversation_id', $conversationId)
                ->where('sender_id', $conversation->etudiant_id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        if ($user->role === 'etudiant' && $conversation->etudiant_id === $user->id) {
            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS
    // ══════════════════════════════════════════════════════════════════════════

    private function formatMsg(Message $m): array
    {
        return [
            'id'              => $m->id,
            'conversation_id' => $m->conversation_id,
            'sender_id'       => $m->sender_id,
            'receiver_id'     => $m->receiver_id,
            'message'         => $m->message,
            'is_read'         => $m->is_read,
            'created_at'      => $m->created_at,
        ];
    }
private function formatConv(Conversation $c, int $conseillerId, string $type): array
{
    $lastMsg = \App\Models\Message::where('conversation_id', $c->id)->latest()->first();
    
    // Utiliser pris_par au lieu de conseiller_nom
    $prisPar = $c->pris_par ?? null;
    
    $unread = $type === 'mes'
        ? \App\Models\Message::where('conversation_id', $c->id)
            ->where('sender_id', $c->etudiant_id)
            ->where('is_read', false)
            ->count()
        : ($type === 'en_attente' 
            ? \App\Models\Message::where('conversation_id', $c->id)
                ->where('is_read', false)
                ->count()
            : 0);

    return [
        'id'                  => $c->id,
        'etudiant_id'         => $c->etudiant_id,
        'etudiant_nom'        => $c->etudiant->name ?? '—',
        'statut'              => $c->statut,
        'pris_par'            => $prisPar,
        'pris_par_moi'        => $c->conseiller_id === $conseillerId,
        'dernier_message'     => $lastMsg?->message ?? '',
        'dernier_message_at'  => $c->dernier_message_at,
        'unread_count'        => $unread,
    ];
}
}