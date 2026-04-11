<template>
    <div class="chat-root">

        <!-- ── Bouton flottant ──────────────────────────────────────── -->
        <button @click="toggleChat" class="chat-fab" :class="{ 'chat-fab--open': isOpen }">
            <transition name="icon-swap" mode="out-in">
                <svg v-if="!isOpen" key="o" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <svg v-else key="c" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </transition>
            <span v-if="unreadCount > 0 && !isOpen" class="chat-badge">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- ── Fenêtre chat ─────────────────────────────────────────── -->
        <transition name="chat-pop">
            <div v-if="isOpen" class="chat-window">

                <!-- ════════════════════════════════
                     CONSEILLER — 3 colonnes
                ════════════════════════════════ -->
                <template v-if="isConseiller">

                    <!-- Sidebar inbox -->
                    <aside class="chat-sidebar">
                        <div class="chat-sidebar__head">
                            <span class="chat-sidebar__title">Conversations</span>
                            <div class="chat-sidebar__actions">
                                <span class="chat-sidebar__count">
                                    {{ enAttente.length + mesConversations.length }}
                                </span>
                                <button @click="closeChat" class="close-chat-btn" title="Fermer">✕</button>
                            </div>
                        </div>

                        <div class="chat-sidebar__list">
                            <div v-if="loadingInbox" class="chat-center-box py-6">
                                <div class="spinner"></div>
                            </div>
                            <template v-else>

                                <!-- Section : En attente -->
                                <div v-if="enAttente.length" class="chat-section-label">
                                    ⏳ En attente ({{ enAttente.length }})
                                </div>
                                <button v-for="c in enAttente" :key="'w-'+c.id"
                                        class="chat-thread chat-thread--waiting"
                                        @click="prendreEtOuvrir(c)">
                                    <div class="chat-thread__av">{{ c.etudiant_nom?.charAt(0).toUpperCase() || '?' }}</div>
                                    <div class="chat-thread__body">
                                        <div class="chat-thread__row">
                                            <span class="chat-thread__name">{{ c.etudiant_nom || 'Étudiant' }}</span>
                                            <span class="chat-thread__time">{{ fmtTime(c.dernier_message_at) }}</span>
                                        </div>
                                        <div class="chat-thread__row">
                                            <span class="chat-thread__preview">{{ c.dernier_message || '—' }}</span>
                                            <span v-if="c.unread_count > 0" class="chat-thread__badge">{{ c.unread_count }}</span>
                                        </div>
                                        <span class="chat-thread__tag chat-thread__tag--waiting">Prendre en charge →</span>
                                    </div>
                                </button>

                                <!-- Section : Mes conversations -->
                                <div v-if="mesConversations.length" class="chat-section-label">
                                    💬 Mes conversations ({{ mesConversations.length }})
                                </div>
                                <button v-for="c in mesConversations" :key="'m-'+c.id"
                                        class="chat-thread"
                                        :class="{ 'chat-thread--active': activeConv?.id === c.id }"
                                        @click="ouvrirConversation(c)">
                                    <div class="chat-thread__av">{{ c.etudiant_nom?.charAt(0).toUpperCase() || '?' }}</div>
                                    <div class="chat-thread__body">
                                        <div class="chat-thread__row">
                                            <span class="chat-thread__name">{{ c.etudiant_nom || 'Étudiant' }}</span>
                                            <span class="chat-thread__time">{{ fmtTime(c.dernier_message_at) }}</span>
                                        </div>
                                        <div class="chat-thread__row">
                                            <span class="chat-thread__preview">{{ c.dernier_message || '—' }}</span>
                                            <span v-if="c.unread_count > 0" class="chat-thread__badge">{{ c.unread_count }}</span>
                                        </div>
                                    </div>
                                </button>

                                <!-- Section : Prises par d'autres -->
                                <div v-if="prisesParAutres.length" class="chat-section-label chat-section-label--muted">
                                    🔒 Prises par d'autres ({{ prisesParAutres.length }})
                                </div>
                                <div v-for="c in prisesParAutres" :key="'a-'+c.id"
                                     class="chat-thread chat-thread--locked">
                                    <div class="chat-thread__av chat-thread__av--locked">{{ c.etudiant_nom?.charAt(0).toUpperCase() || '?' }}</div>
                                    <div class="chat-thread__body">
                                        <div class="chat-thread__row">
                                            <span class="chat-thread__name">{{ c.etudiant_nom || 'Étudiant' }}</span>
                                        </div>
                                        <span class="chat-thread__tag chat-thread__tag--locked">
                                            ✅ Pris par {{ c.pris_par || 'un conseiller' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Aucune conversation -->
                                <div v-if="!enAttente.length && !mesConversations.length && !prisesParAutres.length"
                                     class="chat-center-box py-8">
                                    <span style="font-size:2rem">📭</span>
                                    <p style="font-size:.75rem;margin-top:.4rem;color:var(--ct3);">Aucun message</p>
                                </div>
                            </template>
                        </div>
                    </aside>

                    <!-- Zone conversation -->
                    <div class="chat-conv">
                        <!-- Header -->
                        <div class="chat-conv__head">
                            <template v-if="activeConv">
                                <div class="chat-conv__av">{{ activeConv.etudiant_nom?.charAt(0).toUpperCase() || '?' }}</div>
                                <div style="flex:1;min-width:0">
                                    <p class="chat-conv__name">{{ activeConv.etudiant_nom || 'Étudiant' }}</p>
                                    <p class="chat-conv__sub">Étudiant</p>
                                </div>
                                <button class="btn-profil" :class="{ 'btn-profil--on': showProfil }"
                                        @click="showProfil = !showProfil">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profil
                                </button>
                            </template>
                            <span v-else class="chat-conv__empty-hint">← Choisissez une conversation</span>
                        </div>

                        <!-- Messages -->
                        <div ref="msgBox" class="chat-msgs">
                            <div v-if="!activeConv" class="chat-center-box" style="flex:1">
                                <span style="font-size:2.5rem">👈</span>
                                <p style="font-size:.8rem;margin-top:.4rem;color:var(--ct3)">Sélectionnez une conversation</p>
                            </div>
                            <div v-else-if="loadingMsgs" class="chat-center-box" style="flex:1">
                                <div class="spinner"></div>
                            </div>
                            <template v-else>
                                <div v-if="messages.length === 0" class="chat-center-box" style="flex:1">
                                    <span style="font-size:2rem">💬</span>
                                    <p style="font-size:.78rem;margin-top:.3rem;color:var(--ct3)">Démarrez la conversation</p>
                                    <button v-if="profilData?.message_auto"
                                            class="btn-auto mt-2" @click="sendMsgAuto">
                                        🤖 Message d'accueil
                                    </button>
                                </div>
                                <template v-else>
                                    <div v-for="msg in messages" :key="msg.id"
                                         class="bubble-row"
                                         :class="msg.sender_id === currentUserId ? 'bubble-row--out' : 'bubble-row--in'">
                                        <div class="bubble"
                                             :class="msg.sender_id === currentUserId ? 'bubble--out' : 'bubble--in'">
                                            <p>{{ msg.message }}</p>
                                            <span class="bubble__meta">
                                                {{ fmtTime(msg.created_at) }}
                                                <span v-if="msg.sender_id === currentUserId">
                                                    {{ msg.is_read ? ' ✓✓' : ' ✓' }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Boutons suggestions filières -->
                                    <div v-if="showSuggestions && profilData?.recommandations?.length"
                                         class="smart-btns">
                                        <button v-for="r in profilData.recommandations" :key="r.rang"
                                                class="smart-btn"
                                                @click="insertSuggestion(r)">
                                            {{ ['🥇','🥈','🥉'][r.rang-1] }}
                                            {{ r.filiere.nom }}
                                            <span :class="'score score--'+scoreKey(r.score)">{{ r.score }}%</span>
                                        </button>
                                    </div>
                                </template>
                            </template>
                        </div>

                        <!-- Input conseiller -->
                        <div v-if="activeConv" class="chat-input-zone">
                            <!-- Réponses rapides -->
                            <div class="qr-strip">
                                <button class="qr" @click="newMsg = 'Bonjour ! Comment puis-je vous aider ?'">👋 Bonjour</button>
                                <button class="qr" @click="newMsg = 'Pouvez-vous préciser votre question ?'">❓ Préciser</button>
                                <button v-if="profilData?.recommandations?.[0]" class="qr qr--accent"
                                        @click="newMsg = `Je vous recommande la filière ${profilData.recommandations[0].filiere.nom} (${profilData.recommandations[0].score}% de compatibilité).`">
                                    🏆 Top filière
                                </button>
                                <button class="qr qr--ghost" @click="showSuggestions = !showSuggestions">
                                    🤖 {{ showSuggestions ? 'Masquer' : 'Suggestions' }}
                                </button>
                            </div>
                            <div class="input-row">
                                <input v-model="newMsg" @keyup.enter="sendConseillerMsg"
                                       class="chat-input" type="text"
                                       :placeholder="conseillerPlaceholder" />
                                <button @click="sendConseillerMsg" :disabled="!newMsg.trim()" class="send-btn">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Panneau profil -->
                    <transition name="profil-slide">
                        <div v-if="showProfil && activeConv" class="chat-profil">
                            <p class="profil-head">🧾 Profil étudiant</p>
                            <div v-if="loadingProfil" class="chat-center-box py-4"><div class="spinner"></div></div>
                            <div v-else-if="profilData" class="profil-body">
                                <template v-if="profilData.profil">
                                    <div v-if="profilData.profil.interets?.length" class="ps">
                                        <p class="ps__label">❤️ Intérêts</p>
                                        <div class="tags">
                                            <span v-for="t in profilData.profil.interets.slice(0, 5)" :key="t" class="tag tag--b">{{ t }}</span>
                                        </div>
                                    </div>
                                    <div v-if="profilData.profil.competences?.length" class="ps">
                                        <p class="ps__label">🛠️ Compétences</p>
                                        <div class="tags">
                                            <span v-for="t in profilData.profil.competences.slice(0, 5)" :key="t" class="tag tag--g">{{ t }}</span>
                                        </div>
                                    </div>
                                    <div v-if="profilData.profil.preferences?.length" class="ps">
                                        <p class="ps__label">⚙️ Préférences</p>
                                        <div class="tags">
                                            <span v-for="t in profilData.profil.preferences.slice(0, 5)" :key="t" class="tag tag--p">{{ t }}</span>
                                        </div>
                                    </div>
                                    <div v-if="profilData.recommandations?.length" class="ps">
                                        <p class="ps__label">🏆 Recommandations</p>
                                        <div class="recos">
                                            <div v-for="r in profilData.recommandations" :key="r.rang" class="reco">
                                                <div class="reco__top">
                                                    <span>{{ ['🥇','🥈','🥉'][r.rang-1] }}</span>
                                                    <span class="reco__name">{{ r.filiere.nom }}</span>
                                                    <span :class="'score score--'+scoreKey(r.score)">{{ r.score }}%</span>
                                                </div>
                                                <div class="reco__bar">
                                                    <div :class="'reco__fill reco__fill--'+scoreKey(r.score)"
                                                         :style="{width:r.score+'%'}"></div>
                                                </div>
                                                <p class="reco__lbl">{{ scoreLabel(r.score) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div v-else class="chat-center-box py-4">
                                    <span style="font-size:1.5rem">📝</span>
                                    <p style="font-size:.72rem;margin-top:.3rem;color:var(--ct3);text-align:center">
                                        Questionnaire non rempli
                                    </p>
                                </div>
                            </div>
                        </div>
                    </transition>

                </template>

                <!-- ════════════════════════════════
                     ÉTUDIANT
                ════════════════════════════════ -->
                <template v-else>
                    <div class="etudiant-layout">
                        <div class="etudiant-head">
                            <div class="etudiant-head__av">🎓</div>
                            <div style="flex:1">
                                <p class="etudiant-head__title">Support Orientation</p>
                                <p class="etudiant-head__sub">
                                    {{ convStatut === 'pris_en_charge' ? '🟢 Un conseiller vous répond' : '⏳ En attente de réponse…' }}
                                </p>
                            </div>
                            <button @click="closeChat" class="close-chat-btn-etudiant" title="Fermer">✕</button>
                        </div>

                        <div ref="msgBox" class="chat-msgs chat-msgs--etudiant">
                            <div v-if="loadingMsgs" class="chat-center-box" style="flex:1">
                                <div class="spinner"></div>
                            </div>
                            <div v-else-if="messages.length === 0" class="chat-center-box" style="flex:1">
                                <span style="font-size:2.5rem">💬</span>
                                <p style="font-size:.8rem;margin:.3rem 0 .5rem;color:var(--ct3)">
                                    Envoyez votre premier message
                                </p>
                                <div class="starter-btns">
                                    <button v-for="(q, i) in quickQs" :key="i"
                                            class="starter-btn" @click="newMsg = q">
                                        {{ q }}
                                    </button>
                                </div>
                            </div>
                            <div v-for="msg in messages" :key="msg.id"
                                 class="bubble-row"
                                 :class="msg.sender_id === currentUserId ? 'bubble-row--out' : 'bubble-row--in'">
                                <div class="bubble"
                                     :class="msg.sender_id === currentUserId ? 'bubble--out' : 'bubble--in'">
                                    <p>{{ msg.message }}</p>
                                    <span class="bubble__meta">
                                        {{ fmtTime(msg.created_at) }}
                                        <span v-if="msg.sender_id === currentUserId">
                                            {{ msg.is_read ? ' ✓✓' : ' ✓' }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="chat-input-zone">
                            <div class="input-row">
                                <input v-model="newMsg" @keyup.enter="sendEtudiantMsg"
                                       class="chat-input" type="text"
                                       placeholder="Écrire un message…" />
                                <button @click="sendEtudiantMsg" :disabled="!newMsg.trim()" class="send-btn">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="input-hint">Entrée pour envoyer</p>
                        </div>
                    </div>
                </template>

            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'

// ── Auth ──────────────────────────────────────────────────────────────────────
const page            = usePage()
const currentUserId   = page.props.auth.user.id
const currentUserRole = page.props.auth.user.role
const isConseiller    = computed(() => currentUserRole === 'conseiller')

// ── UI global ─────────────────────────────────────────────────────────────────
const isOpen       = ref(false)
const unreadCount  = ref(0)
const newMsg       = ref('')
const msgBox       = ref(null)
const showProfil   = ref(true)
const showSuggestions = ref(false)

// ── Conseiller ────────────────────────────────────────────────────────────────
const enAttente        = ref([])
const mesConversations = ref([])
const prisesParAutres  = ref([])
const loadingInbox     = ref(false)
const activeConv       = ref(null)
const messages         = ref([])
const loadingMsgs      = ref(false)
const profilData       = ref(null)
const loadingProfil    = ref(false)

// ── Étudiant ──────────────────────────────────────────────────────────────────
const convStatut    = ref(null)   // 'en_attente' | 'pris_en_charge' | null
const convId        = ref(null)
const quickQs       = [
    '📚 Quelles filières correspondent à mon profil ?',
    '🔢 Comment est calculé mon score ?',
    '🎓 Quels sont les débouchés de la filière recommandée ?',
]

// ── Polling ───────────────────────────────────────────────────────────────────
let pollingIntervalId = null
let lastActivityTimestamp = Date.now()

// ── Helpers ───────────────────────────────────────────────────────────────────
// Récupération du token CSRF depuis la balise meta
const csrf = () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (!token) console.warn('CSRF token non trouvé')
    return token || ''
}

const fmtTime = (d) => {
    if (!d) return ''
    const date = new Date(d)
    const now = new Date()
    const diffMs = now - date
    const diffMin = Math.floor(diffMs / 60000)
    
    if (diffMin < 1) return 'À l\'instant'
    if (diffMin < 60) return `${diffMin} min`
    if (diffMin < 1440) return `${Math.floor(diffMin / 60)}h`
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
}

const scoreKey   = (s) => s >= 80 ? 'green' : s >= 60 ? 'yellow' : 'red'
const scoreLabel = (s) => s >= 80 ? '✅ Très compatible' : s >= 60 ? '👍 Compatible' : '⚠️ À discuter'

const scrollBottom = async () => {
    await nextTick()
    if (msgBox.value) {
        msgBox.value.scrollTop = msgBox.value.scrollHeight
    }
}

// Placeholder pour le conseiller (propriété calculée)
const conseillerPlaceholder = computed(() => {
    const nom = activeConv.value?.etudiant_nom || "l'étudiant"
    return `Répondre à ${nom}…`
})

// Requêtes HTTP avec gestion CSRF
const post = async (url, body = {}) => {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    })
    
    if (!response.ok) {
        let errorMsg = `HTTP ${response.status}`
        try {
            const err = await response.json()
            errorMsg = err.message || err.error || errorMsg
        } catch (e) {}
        throw new Error(errorMsg)
    }
    return response.json()
}

const get = async (url) => {
    const response = await fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
    }
    return response.json()
}

// ══════════════════════════════════════════════════════════════════════════════
// TOGGLE & POLLING
// ══════════════════════════════════════════════════════════════════════════════

const startPolling = () => {
    if (pollingIntervalId) clearInterval(pollingIntervalId)
    if (!isOpen.value) return
    
    pollingIntervalId = setInterval(async () => {
        if (!isOpen.value) {
            stopPolling()
            return
        }
        
        // Réduire la fréquence si inactif
        if (Date.now() - lastActivityTimestamp > 30000) {
            return
        }
        
        try {
            if (isConseiller.value) {
                // Mise à jour légère de la sidebar
                if (!activeConv.value) {
                    await loadInbox()
                } else {
                    await loadInbox()
                    const d = await get(`/api/chat/messages/${activeConv.value.id}`)
                    const newMsgs = d.messages ?? []
                    if (JSON.stringify(newMsgs) !== JSON.stringify(messages.value)) {
                        messages.value = newMsgs
                        await scrollBottom()
                        lastActivityTimestamp = Date.now()
                    }
                    if (d.profil && JSON.stringify(d.profil) !== JSON.stringify(profilData.value)) {
                        profilData.value = d.profil
                    }
                }
            } else {
                // Étudiant
                if (convId.value) {
                    const d = await get(`/api/chat/messages/${convId.value}`)
                    const newMsgs = d.messages ?? []
                    if (JSON.stringify(newMsgs) !== JSON.stringify(messages.value)) {
                        messages.value = newMsgs
                        convStatut.value = d.conversation?.statut ?? convStatut.value
                        await scrollBottom()
                        lastActivityTimestamp = Date.now()
                    }
                } else {
                    await loadEtudiantConv()
                }
            }
            await updateUnread()
        } catch (e) {
            console.error('Polling error:', e)
        }
    }, 5000)
}

const stopPolling = () => {
    if (pollingIntervalId) {
        clearInterval(pollingIntervalId)
        pollingIntervalId = null
    }
}

const updateUnread = async () => {
    try {
        const d = await get('/api/chat/unread-count')
        unreadCount.value = d.unread_count ?? 0
    } catch (e) {
        // Ignorer les erreurs (ex: 401 si non connecté)
        console.debug('unread-count error:', e.message)
    }
}

const onUserActivity = () => {
    lastActivityTimestamp = Date.now()
}

const toggleChat = async () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        lastActivityTimestamp = Date.now()
        if (isConseiller.value) {
            await loadInbox()
        } else {
            await loadEtudiantConv()
        }
        startPolling()
    } else {
        stopPolling()
        activeConv.value = null
        profilData.value = null
        messages.value = []
    }
}

const closeChat = () => {
    isOpen.value = false
    stopPolling()
    activeConv.value = null
    profilData.value = null
    messages.value = []
    convId.value = null
    convStatut.value = null
}

// ══════════════════════════════════════════════════════════════════════════════
// CONSEILLER
// ══════════════════════════════════════════════════════════════════════════════

const loadInbox = async () => {
    if (loadingInbox.value) return
    loadingInbox.value = true
    try {
        const d = await get('/api/chat/conseiller/conversations')
        enAttente.value        = d.en_attente        ?? []
        mesConversations.value = d.mes_conversations ?? []
        prisesParAutres.value  = d.prises_par_autres ?? []
    } catch (e) { 
        console.error('loadInbox:', e) 
    } finally { 
        loadingInbox.value = false 
    }
}

const prendreEtOuvrir = async (c) => {
    stopPolling()
    loadingInbox.value = true
    
    try {
        const d = await post('/api/chat/conseiller/prendre', {
            conversation_id: c.id,
        })
        
        if (d.success) {
            await loadInbox()
            const conv = mesConversations.value.find(x => x.id === c.id)
            if (conv) {
                await ouvrirConversation(conv)
            } else {
                // Recharger une fois de plus
                await loadInbox()
                const conv2 = mesConversations.value.find(x => x.id === c.id)
                if (conv2) await ouvrirConversation(conv2)
            }
        } else {
            alert(`⚠️ ${d.message || 'Cette conversation a déjà été prise'}`)
            await loadInbox()
        }
    } catch (e) {
        console.error('prendreEtOuvrir:', e)
        alert('Erreur lors de la prise de conversation: ' + e.message)
    } finally {
        loadingInbox.value = false
        startPolling()
    }
}

const ouvrirConversation = async (c) => {
    if (activeConv.value?.id === c.id) return
    
    activeConv.value = c
    messages.value = []
    profilData.value = null
    loadingMsgs.value = true
    loadingProfil.value = true
    showProfil.value = true
    
    try {
        const d = await get(`/api/chat/messages/${c.id}`)
        messages.value = d.messages ?? []
        profilData.value = d.profil ?? null
        lastActivityTimestamp = Date.now()
        
        // Marquer comme lus (avec gestion silencieuse des erreurs CSRF)
        try {
            await post(`/api/chat/marquer-lus/${c.id}`)
        } catch (e) {
            console.warn('markAsRead failed:', e.message)
        }
        
        const idx = mesConversations.value.findIndex(x => x.id === c.id)
        if (idx !== -1) mesConversations.value[idx].unread_count = 0
        
        await scrollBottom()
    } catch (e) {
        console.error('ouvrirConversation error:', e)
        alert('Erreur chargement conversation: ' + e.message)
    } finally {
        loadingMsgs.value = false
        loadingProfil.value = false
    }
}

const sendConseillerMsg = async () => {
    if (!newMsg.value.trim() || !activeConv.value) return
    
    const text = newMsg.value
    newMsg.value = ''
    stopPolling()
    
    try {
        const d = await post('/api/chat/conseiller/send', {
            conversation_id: activeConv.value.id,
            message: text,
        })
        
        if (d.success) {
            messages.value.push(d.message)
            await scrollBottom()
            lastActivityTimestamp = Date.now()
            
            const idx = mesConversations.value.findIndex(x => x.id === activeConv.value.id)
            if (idx !== -1) {
                mesConversations.value[idx].dernier_message = text
                mesConversations.value[idx].dernier_message_at = new Date().toISOString()
            }
        } else {
            newMsg.value = text
            alert('Erreur lors de l\'envoi du message')
        }
    } catch (e) {
        console.error('sendConseillerMsg:', e)
        newMsg.value = text
        alert('Erreur réseau: ' + e.message)
    } finally {
        startPolling()
    }
}

const sendMsgAuto = async () => {
    if (!profilData.value?.message_auto) return
    newMsg.value = profilData.value.message_auto
    await sendConseillerMsg()
}

const insertSuggestion = (r) => {
    const lbl = r.score >= 80 ? `correspond très bien à votre profil (${r.score}%)`
              : r.score >= 60 ? `est une bonne option (${r.score}%)`
              :                 `mérite réflexion (${r.score}%)`
    newMsg.value = `📌 La filière ${r.filiere.nom} ${lbl}.`
}

// ══════════════════════════════════════════════════════════════════════════════
// ÉTUDIANT
// ══════════════════════════════════════════════════════════════════════════════

const loadEtudiantConv = async () => {
    if (loadingMsgs.value) return
    loadingMsgs.value = true
    try {
        const d = await get('/api/chat/etudiant/conversation')
        convId.value     = d.conversation?.id   ?? null
        convStatut.value = d.conversation?.statut ?? null
        messages.value   = d.messages ?? []
        await scrollBottom()
    } catch (e) { 
        console.error('loadEtudiantConv:', e) 
    } finally { 
        loadingMsgs.value = false 
    }
}

const sendEtudiantMsg = async () => {
    if (!newMsg.value.trim()) return
    
    const text = newMsg.value
    newMsg.value = ''
    stopPolling()
    
    try {
        const d = await post('/api/chat/etudiant/send', { message: text })
        if (d.success) {
            convId.value     = d.conversation_id
            convStatut.value = d.statut
            messages.value.push(d.message)
            await scrollBottom()
            lastActivityTimestamp = Date.now()
        } else {
            newMsg.value = text
            alert('Erreur lors de l\'envoi')
        }
    } catch (e) {
        console.error('sendEtudiantMsg:', e)
        newMsg.value = text
        alert('Erreur réseau: ' + e.message)
    } finally {
        startPolling()
    }
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
    updateUnread()
    setInterval(updateUnread, 30000)
    window.addEventListener('click', onUserActivity)
    window.addEventListener('keypress', onUserActivity)
})

onUnmounted(() => {
    stopPolling()
    window.removeEventListener('click', onUserActivity)
    window.removeEventListener('keypress', onUserActivity)
})
</script>

<style scoped>
/* ── Variables ───────────────────────────────────────────── */
.chat-root {
    --cp: #4f46e5; --cpd: #3730a3; --cpl: #e0e7ff;
    --cs: #fff; --cs2: #f8fafc; --cb: #e2e8f0;
    --ct: #0f172a; --ct2: #64748b; --ct3: #94a3b8;
    --green: #22c55e; --yellow: #f59e0b; --red: #ef4444;
    --r: 14px;
    --shadow: 0 24px 64px rgba(0,0,0,.18), 0 4px 16px rgba(0,0,0,.08);
    position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
    font-family: 'Segoe UI', system-ui, sans-serif; font-size: 14px;
}
:global(.dark) .chat-root {
    --cs: #1e293b; --cs2: #0f172a; --cb: #334155;
    --ct: #f1f5f9; --ct2: #94a3b8; --ct3: #64748b; --cpl: #1e1b4b;
}

/* FAB */
.chat-fab {
    position: relative; width: 56px; height: 56px; border-radius: 50%;
    background: var(--cp); color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 20px rgba(79,70,229,.45);
    transition: all .2s;
}
.chat-fab svg { width: 24px; height: 24px; }
.chat-fab:hover { background: var(--cpd); transform: scale(1.07); }
.chat-fab--open { background: #ef4444; }
.chat-badge {
    position: absolute; top: -5px; right: -5px;
    background: #ef4444; color: #fff; font-size: 10px; font-weight: 700;
    min-width: 20px; height: 20px; border-radius: 10px; padding: 0 4px;
    display: flex; align-items: center; justify-content: center;
    animation: pulse 1.4s infinite;
}

/* Fenêtre */
.chat-window {
    position: absolute; bottom: 68px; right: 0;
    background: var(--cs); border-radius: var(--r);
    box-shadow: var(--shadow); border: 1px solid var(--cb);
    display: flex; overflow: hidden;
    width: min(820px, calc(100vw - 3rem));
    height: min(570px, calc(100vh - 5rem));
}
.chat-root:has(.etudiant-layout) .chat-window {
    width: min(360px, calc(100vw - 2rem));
    height: min(530px, calc(100vh - 5rem));
}
@media (max-width: 860px) {
    .chat-window { width: calc(100vw - 2rem); }
    .chat-profil  { display: none; }
}

/* Sidebar */
.chat-sidebar {
    width: 240px; min-width: 240px; border-right: 1px solid var(--cb);
    display: flex; flex-direction: column; background: var(--cs2);
}
.chat-sidebar__head {
    padding: .65rem .9rem; border-bottom: 1px solid var(--cb);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--cs);
}
.chat-sidebar__title { font-size: .7rem; font-weight: 700; color: var(--ct2); text-transform: uppercase; letter-spacing: .05em; }
.chat-sidebar__actions { display: flex; align-items: center; gap: 8px; }
.chat-sidebar__count { background: var(--cp); color: #fff; font-size: .65rem; padding: 1px 7px; border-radius: 9px; }
.close-chat-btn {
    background: none; border: none; font-size: 16px; cursor: pointer;
    color: var(--ct3); padding: 0 5px; line-height: 1; border-radius: 4px;
}
.close-chat-btn:hover { background: var(--cb); color: var(--ct); }
.close-chat-btn-etudiant {
    background: rgba(255,255,255,.2); border: none; font-size: 18px;
    cursor: pointer; color: #fff; padding: 0 8px; line-height: 1;
    border-radius: 6px; height: 28px;
}
.close-chat-btn-etudiant:hover { background: rgba(255,255,255,.3); }

.chat-sidebar__list { flex: 1; overflow-y: auto; }

/* Section labels */
.chat-section-label {
    padding: .3rem .75rem .2rem;
    font-size: .62rem; font-weight: 700; color: var(--ct3);
    text-transform: uppercase; letter-spacing: .05em;
    background: var(--cs2); border-bottom: 1px solid var(--cb);
    position: sticky; top: 0; z-index: 1;
}
.chat-section-label--muted { opacity: .7; }

/* Thread */
.chat-thread {
    width: 100%; text-align: left; border: none; background: none; cursor: pointer;
    padding: .6rem .8rem; border-bottom: 1px solid var(--cb);
    display: flex; align-items: center; gap: .5rem; transition: background .12s;
}
.chat-thread:hover     { background: var(--cpl); }
.chat-thread--active   { background: var(--cpl); border-left: 3px solid var(--cp); }
.chat-thread--waiting  { border-left: 3px solid var(--yellow); }
.chat-thread--waiting:hover { background: #fef9c3; }
.chat-thread--locked   { opacity: .6; cursor: default; }

.chat-thread__av {
    width: 32px; height: 32px; min-width: 32px; border-radius: 50%;
    background: linear-gradient(135deg, var(--cp), #818cf8);
    color: #fff; font-size: .8rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.chat-thread__av--locked { background: var(--cb); color: var(--ct3); }
.chat-thread__body { flex: 1; min-width: 0; }
.chat-thread__row  { display: flex; justify-content: space-between; align-items: center; gap: .25rem; }
.chat-thread__name { font-size: .76rem; font-weight: 600; color: var(--ct); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.chat-thread__time { font-size: .6rem; color: var(--ct3); flex-shrink: 0; }
.chat-thread__preview { font-size: .68rem; color: var(--ct2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.chat-thread__badge { background: var(--cp); color: #fff; font-size: .6rem; padding: 0 5px; height: 15px; border-radius: 8px; display: flex; align-items: center; flex-shrink: 0; }
.chat-thread__tag  { display: inline-block; font-size: .6rem; padding: 1px 5px; border-radius: 6px; margin-top: 2px; font-weight: 600; }
.chat-thread__tag--waiting { background: #fef3c7; color: #92400e; }
.chat-thread__tag--locked  { background: #dcfce7; color: #166534; }

/* Conversation */
.chat-conv { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.chat-conv__head {
    padding: .6rem .9rem; border-bottom: 1px solid var(--cb);
    background: var(--cs); display: flex; align-items: center; gap: .55rem; min-height: 52px;
}
.chat-conv__av {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg, var(--cp), #818cf8);
    color: #fff; font-size: .78rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.chat-conv__name  { font-size: .82rem; font-weight: 600; color: var(--ct); }
.chat-conv__sub   { font-size: .65rem; color: var(--ct3); }
.chat-conv__empty-hint { font-size: .78rem; color: var(--ct3); }
.btn-profil {
    display: flex; align-items: center; gap: .25rem;
    padding: .22rem .6rem; border-radius: 18px;
    background: var(--cs2); border: 1px solid var(--cb);
    font-size: .68rem; font-weight: 600; color: var(--ct2); cursor: pointer;
}
.btn-profil:hover, .btn-profil--on { background: var(--cpl); color: var(--cp); border-color: var(--cp); }

/* Messages */
.chat-msgs {
    flex: 1; overflow-y: auto; padding: .75rem;
    display: flex; flex-direction: column; gap: .3rem;
    background: var(--cs2); scroll-behavior: smooth;
}
.chat-center-box {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: var(--ct3); text-align: center; flex: 1;
}
.bubble-row { display: flex; }
.bubble-row--out { justify-content: flex-end; }
.bubble-row--in  { justify-content: flex-start; }
.bubble {
    max-width: 78%; padding: .5rem .75rem; border-radius: 12px;
    font-size: .78rem; line-height: 1.5;
}
.bubble p { margin: 0; word-break: break-word; white-space: pre-line; }
.bubble--out { background: var(--cp); color: #fff; border-bottom-right-radius: 3px; }
.bubble--in  { background: var(--cs); color: var(--ct); border-bottom-left-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,.07); }
.bubble__meta { display: block; font-size: .6rem; margin-top: .18rem; opacity: .6; text-align: right; }

/* Smart buttons */
.smart-btns { display: flex; flex-wrap: wrap; gap: .3rem; padding-top: .2rem; }
.smart-btn {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .25rem .55rem; background: var(--cs); border: 1px solid var(--cb);
    border-radius: 18px; font-size: .68rem; color: var(--ct); cursor: pointer;
}
.smart-btn:hover { border-color: var(--cp); color: var(--cp); background: var(--cpl); }

/* Input */
.chat-input-zone { padding: .5rem .65rem; border-top: 1px solid var(--cb); background: var(--cs); }
.qr-strip { display: flex; flex-wrap: wrap; gap: .28rem; margin-bottom: .4rem; }
.qr {
    padding: .15rem .5rem; border-radius: 18px; font-size: .65rem; font-weight: 500;
    border: 1px solid var(--cb); background: var(--cs2); color: var(--ct2); cursor: pointer;
}
.qr:hover { border-color: var(--cp); color: var(--cp); }
.qr--accent { background: var(--cpl); color: var(--cp); border-color: var(--cp); }
.qr--ghost  { border-style: dashed; }
.input-row { display: flex; gap: .4rem; }
.chat-input {
    flex: 1; padding: .48rem .8rem;
    border: 1.5px solid var(--cb); border-radius: 22px;
    font-size: .78rem; background: var(--cs2); color: var(--ct); outline: none;
}
.chat-input:focus { border-color: var(--cp); }
.send-btn {
    width: 35px; height: 35px; border-radius: 50%;
    background: var(--cp); color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}
.send-btn svg { width: 16px; height: 16px; }
.send-btn:hover  { background: var(--cpd); }
.send-btn:disabled { opacity: .38; cursor: default; }
.input-hint { font-size: .6rem; color: var(--ct3); text-align: center; margin-top: .25rem; }

/* Profil */
.chat-profil { width: 210px; min-width: 210px; border-left: 1px solid var(--cb); background: var(--cs2); overflow: hidden; display: flex; flex-direction: column; }
.profil-head { padding: .55rem .75rem; font-size: .65rem; font-weight: 700; color: var(--ct2); text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--cb); background: var(--cs); margin: 0; }
.profil-body { flex: 1; overflow-y: auto; padding: .5rem; }
.ps { margin-bottom: .65rem; }
.ps__label { font-size: .6rem; font-weight: 700; color: var(--ct3); text-transform: uppercase; margin: 0 0 .25rem; }
.tags { display: flex; flex-wrap: wrap; gap: .18rem; }
.tag { font-size: .6rem; padding: 2px 6px; border-radius: 8px; font-weight: 500; }
.tag--b { background: #dbeafe; color: #1d4ed8; }
.tag--g { background: #dcfce7; color: #15803d; }
.tag--p { background: #f3e8ff; color: #7e22ce; }
:global(.dark) .tag--b { background: #1e3a5f; color: #93c5fd; }
:global(.dark) .tag--g { background: #14532d; color: #86efac; }
:global(.dark) .tag--p { background: #3b0764; color: #d8b4fe; }
.recos { display: flex; flex-direction: column; gap: .4rem; }
.reco { background: var(--cs); border: 1px solid var(--cb); border-radius: 7px; padding: .4rem; }
.reco__top { display: flex; align-items: center; gap: .25rem; margin-bottom: .18rem; }
.reco__name { flex: 1; font-size: .68rem; font-weight: 600; color: var(--ct); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.reco__bar { height: 4px; background: var(--cb); border-radius: 3px; overflow: hidden; margin-bottom: .14rem; }
.reco__fill { height: 100%; border-radius: 3px; }
.reco__fill--green  { background: var(--green); }
.reco__fill--yellow { background: var(--yellow); }
.reco__fill--red    { background: var(--red); }
.reco__lbl { font-size: .58rem; color: var(--ct3); margin: 0; }
.score { font-size: .68rem; font-weight: 700; flex-shrink: 0; }
.score--green  { color: var(--green); }
.score--yellow { color: var(--yellow); }
.score--red    { color: var(--red); }
.btn-auto {
    display: inline-block; padding: .32rem .7rem;
    background: var(--cp); color: #fff; font-size: .7rem; font-weight: 600;
    border: none; border-radius: 16px; cursor: pointer;
}
.btn-auto:hover { background: var(--cpd); }
.btn-auto--full { width: 100%; text-align: center; }
.mt-2 { margin-top: .5rem; }

/* Étudiant */
.etudiant-layout { display: flex; flex-direction: column; width: 100%; height: 100%; }
.etudiant-head {
    padding: .7rem 1rem;
    background: linear-gradient(135deg, #4f46e5, #818cf8);
    color: #fff; display: flex; align-items: center; gap: .55rem;
}
.etudiant-head__av { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.etudiant-head__title { font-size: .86rem; font-weight: 700; margin: 0; }
.etudiant-head__sub   { font-size: .65rem; opacity: .85; margin: 0; }
.starter-btns { display: flex; flex-direction: column; gap: .3rem; width: 100%; padding: 0 .5rem; }
.starter-btn {
    text-align: left; font-size: .7rem; padding: .35rem .6rem;
    background: var(--cs); border: 1px solid var(--cb); border-radius: 8px;
    cursor: pointer; color: var(--ct2);
}
.starter-btn:hover { border-color: var(--cp); color: var(--cp); background: var(--cpl); }

/* Spinner */
.spinner {
    width: 24px; height: 24px; border-radius: 50%;
    border: 3px solid var(--cb); border-top-color: var(--cp);
    animation: spin .7s linear infinite;
}
.py-4 { padding: 1rem 0; }
.py-6 { padding: 1.5rem 0; }
.py-8 { padding: 2rem 0; }

/* Transitions */
.chat-pop-enter-active { animation: pop-in .22s cubic-bezier(.34,1.56,.64,1); }
.chat-pop-leave-active { animation: pop-in .15s ease-in reverse; }
@keyframes pop-in { from { opacity:0; transform: scale(.88) translateY(14px); } to { opacity:1; transform: scale(1) translateY(0); } }
.icon-swap-enter-active, .icon-swap-leave-active { transition: opacity .14s, transform .14s; }
.icon-swap-enter-from { opacity:0; transform: rotate(-45deg) scale(.6); }
.icon-swap-leave-to   { opacity:0; transform: rotate(45deg)  scale(.6); }
.profil-slide-enter-active, .profil-slide-leave-active { transition: width .2s ease, opacity .2s; overflow: hidden; }
.profil-slide-enter-from, .profil-slide-leave-to { width: 0 !important; opacity: 0; }
@keyframes pulse { 0%,100% { transform:scale(1); } 50% { transform:scale(1.2); } }
@keyframes spin  { to { transform:rotate(360deg); } }
</style>