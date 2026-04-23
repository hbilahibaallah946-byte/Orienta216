<template>
    <div class="cw-root">
        <!-- FAB -->
        <button @click="toggleWall" class="cw-fab" :class="{ 'cw-fab--open': isOpen }" title="Mur des étudiants">
            <transition name="icon-swap" mode="out-in">
                <svg v-if="!isOpen" key="o" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>
                </svg>
                <svg v-else key="c" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </transition>
            <span v-if="unreadNew > 0 && !isOpen" class="cw-fab__badge">{{ unreadNew > 9 ? '9+' : unreadNew }}</span>
        </button>

        <!-- Panel -->
        <transition name="wall-pop">
            <div v-if="isOpen" class="cw-panel">

                <!-- Header -->
                <div class="cw-header">
                    <div class="cw-header__left">
                        <div class="cw-header__icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:17px;height:17px">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="cw-header__title">Mur des étudiants</h3>
                            <p class="cw-header__sub">{{ allCount }} publication{{ allCount !== 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                    <button @click="isOpen = false" class="cw-close-btn" title="Fermer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Compose -->
                <div class="cw-compose">
                    <div class="cw-av" :style="{ background: avatarColor(currentUserName) }">{{ initial(currentUserName) }}</div>
                    <div class="cw-compose__inner">
                        <transition name="reply-slide">
                            <div v-if="replyingTo" class="cw-reply-tag">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                <span>Répondre à <strong>{{ replyingTo.user_name }}</strong></span>
                                <button @click="cancelReply" class="cw-reply-tag__x">✕</button>
                            </div>
                        </transition>
                        <textarea
                            v-model="newBody"
                            :placeholder="replyingTo ? `@${replyingTo.user_name} …` : 'Partagez une question, conseil ou expérience…'"
                            class="cw-textarea"
                            rows="1"
                            @keydown.ctrl.enter.prevent="submitComment"
                            @keydown.meta.enter.prevent="submitComment"
                            @input="autoResize"
                            ref="mainTextarea"
                        ></textarea>
                        <div class="cw-compose__bar">
                            <span class="cw-char" :class="{ 'cw-char--warn': newBody.length > 900 }">{{ newBody.length }}/1000</span>
                            <span class="cw-hint">Ctrl+↵ pour publier</span>
                            <button @click="submitComment" :disabled="!newBody.trim() || sending" class="cw-post-btn">
                                <span v-if="sending" class="cw-spin-xs"></span>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Publier</span>
                            </button>
                        </div>
                        <p v-if="postError" class="cw-err">{{ postError }}</p>
                    </div>
                </div>

                <!-- Feed -->
                <div class="cw-feed" ref="feedEl">
                    <div v-if="loading" class="cw-center"><div class="cw-spinner"></div></div>

                    <div v-else-if="!comments.length" class="cw-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" style="width:40px;height:40px;opacity:.25"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <p class="cw-empty__t">Aucun commentaire</p>
                        <p class="cw-empty__s">Soyez le premier à partager quelque chose !</p>
                    </div>

                    <template v-else>
                        <div class="cw-list">
                            <div v-for="c in comments" :key="c.id" class="cw-card">
                                <!-- Root comment -->
                                <div class="cw-item">
                                    <div class="cw-item__av" :style="{ background: avatarColor(c.user_name) }">{{ initial(c.user_name) }}</div>
                                    <div class="cw-item__body">
                                        <div class="cw-item__meta">
                                            <span class="cw-item__name">{{ c.user_name }}</span>
                                            <span v-if="c.user_id === currentUserId" class="cw-item__you">vous</span>
                                            <span class="cw-item__time">{{ fmtTime(c.created_at) }}</span>
                                            <span v-if="c.reported" class="cw-item__flag" title="Signalé">⚑</span>
                                        </div>
                                        <p class="cw-item__text" v-html="highlightMentions(c.body)"></p>
                                        <div class="cw-item__actions">
                                            <button @click="react(c,1)" class="cw-btn" :class="{'cw-btn--liked':c.user_reaction===1}" title="J'aime">
                                                <svg viewBox="0 0 20 20" :fill="c.user_reaction===1?'currentColor':'none'" stroke="currentColor" stroke-width="1.5" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
                                                <span>{{ c.likes_count || '' }}</span>
                                            </button>
                                            <button @click="react(c,-1)" class="cw-btn" :class="{'cw-btn--disliked':c.user_reaction===-1}" title="Je n'aime pas">
                                                <svg viewBox="0 0 20 20" :fill="c.user_reaction===-1?'currentColor':'none'" stroke="currentColor" stroke-width="1.5" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.105-1.79l-.05-.025A4 4 0 0011.055 2H5.64a2 2 0 00-1.962 1.608l-1.2 6A2 2 0 004.44 12H8v4a2 2 0 002 2 1 1 0 001-1v-.667a4 4 0 01.8-2.4l1.4-1.866a4 4 0 00.8-2.4z"/></svg>
                                                <span>{{ c.dislikes_count || '' }}</span>
                                            </button>
                                            <button @click="startReply(c)" class="cw-btn" title="Répondre">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                <span>Répondre</span>
                                            </button>
                                            <button v-if="c.user_id !== currentUserId" @click="reportComment(c)" class="cw-btn cw-btn--report" :class="{'cw-btn--reported':c.reported}" title="Signaler">
                                                <svg viewBox="0 0 24 24" :fill="c.reported?'currentColor':'none'" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                            </button>
                                            <button v-if="c.user_id === currentUserId || isAdmin" @click="deleteComment(c,null)" class="cw-btn cw-btn--del" title="Supprimer">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Replies -->
                                <div v-if="c.replies && c.replies.length" class="cw-replies">
                                    <div v-for="r in c.replies" :key="r.id" class="cw-item cw-item--reply">
                                        <div class="cw-item__av cw-item__av--sm" :style="{ background: avatarColor(r.user_name) }">{{ initial(r.user_name) }}</div>
                                        <div class="cw-item__body">
                                            <div class="cw-item__meta">
                                                <span class="cw-item__name">{{ r.user_name }}</span>
                                                <span v-if="r.user_id === currentUserId" class="cw-item__you">vous</span>
                                                <span class="cw-item__time">{{ fmtTime(r.created_at) }}</span>
                                            </div>
                                            <p class="cw-item__text" v-html="highlightMentions(r.body)"></p>
                                            <div class="cw-item__actions">
                                                <button @click="react(r,1)" class="cw-btn" :class="{'cw-btn--liked':r.user_reaction===1}">
                                                    <svg viewBox="0 0 20 20" :fill="r.user_reaction===1?'currentColor':'none'" stroke="currentColor" stroke-width="1.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
                                                    <span>{{ r.likes_count || '' }}</span>
                                                </button>
                                                <button @click="react(r,-1)" class="cw-btn" :class="{'cw-btn--disliked':r.user_reaction===-1}">
                                                    <svg viewBox="0 0 20 20" :fill="r.user_reaction===-1?'currentColor':'none'" stroke="currentColor" stroke-width="1.5" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.105-1.79l-.05-.025A4 4 0 0011.055 2H5.64a2 2 0 00-1.962 1.608l-1.2 6A2 2 0 004.44 12H8v4a2 2 0 002 2 1 1 0 001-1v-.667a4 4 0 01.8-2.4l1.4-1.866a4 4 0 00.8-2.4z"/></svg>
                                                    <span>{{ r.dislikes_count || '' }}</span>
                                                </button>
                                                <button v-if="r.user_id !== currentUserId" @click="reportComment(r)" class="cw-btn cw-btn--report" :class="{'cw-btn--reported':r.reported}">
                                                    <svg viewBox="0 0 24 24" :fill="r.reported?'currentColor':'none'" stroke="currentColor" stroke-width="2" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                                </button>
                                                <button v-if="r.user_id === currentUserId || isAdmin" @click="deleteComment(r,c)" class="cw-btn cw-btn--del">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="hasMore" class="cw-more">
                            <button @click="loadMore" :disabled="loadingMore" class="cw-more-btn">
                                <span v-if="loadingMore" class="cw-spin-xs cw-spin-xs--dark"></span>
                                <span v-else>Voir plus de commentaires</span>
                            </button>
                        </div>
                    </template>
                </div>

            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'

// ── Auth — computed so it re-reads after account switch ───────────────────────
const page            = usePage()
const currentUserId   = computed(() => page.props.auth?.user?.id)
const currentUserName = computed(() => page.props.auth?.user?.name || '?')
const isAdmin         = computed(() => page.props.auth?.user?.role === 'admin')

// ── State ─────────────────────────────────────────────────────────────────────
const isOpen       = ref(false)
const comments     = ref([])
const loading      = ref(false)
const loadingMore  = ref(false)
const sending      = ref(false)
const newBody      = ref('')
const replyingTo   = ref(null)
const feedEl       = ref(null)
const mainTextarea = ref(null)
const currentPage  = ref(1)
const lastPage     = ref(1)
const unreadNew    = ref(0)
const postError    = ref('')

const allCount = computed(() =>
    comments.value.reduce((acc, c) => acc + 1 + (c.replies?.length ?? 0), 0)
)
const hasMore = computed(() => currentPage.value < lastPage.value)

// ── CSRF — always read FRESH from DOM, never cache ────────────────────────────
const getCsrf = () => {
    const meta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (meta) return meta
    const ck = document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN='))
    return ck ? decodeURIComponent(ck.split('=')[1]) : ''
}

const refreshCsrf = async () => {
    try {
        const r = await fetch('/csrf-token', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        const d = await r.json()
        if (d?.token) document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', d.token)
    } catch {}
}

const mkHeaders = () => ({
    'Content-Type':     'application/json',
    'Accept':           'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN':     getCsrf(),   // fresh every call
    'X-XSRF-TOKEN':     getCsrf(),
})

const apiPost = async (url, body = {}, retry = true) => {
    const res = await fetch(url, { method: 'POST', headers: mkHeaders(), credentials: 'same-origin', body: JSON.stringify(body) })
    if (res.status === 419 && retry) { await refreshCsrf(); return apiPost(url, body, false) }
    if (res.status === 401) throw new Error('SESSION_EXPIRED')
    if (!res.ok) { const e = await res.json().catch(()=>({})); throw new Error(e.message || `HTTP ${res.status}`) }
    return res.json()
}

const apiDelete = async (url, retry = true) => {
    const res = await fetch(url, { method: 'DELETE', headers: mkHeaders(), credentials: 'same-origin' })
    if (res.status === 419 && retry) { await refreshCsrf(); return apiDelete(url, false) }
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    return res.json()
}

const apiGet = async (url) => {
    const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    return res.json()
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const PALETTE = ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#6366f1']
const avatarColor = (n) => PALETTE[[...(n||'')].reduce((a,c)=>a+c.charCodeAt(0),0) % PALETTE.length]
const initial     = (n) => (n||'?').charAt(0).toUpperCase()
const fmtTime = (d) => {
    if (!d) return ''
    const s = Math.floor((Date.now() - new Date(d)) / 1000)
    if (s < 60)    return 'À l\'instant'
    if (s < 3600)  return `${Math.floor(s/60)} min`
    if (s < 86400) return `${Math.floor(s/3600)}h`
    return new Date(d).toLocaleDateString('fr-FR', { day:'2-digit', month:'short' })
}
const highlightMentions = (t) => (t||'').replace(/@(\S+)/g, '<span class="cw-mention">@$1</span>')
const normalize = (c) => ({
    id: c.id, body: c.body, parent_id: c.parent_id ?? null,
    user_id: c.user_id, user_name: c.user?.name ?? c.user_name ?? '?',
    created_at: c.created_at,
    likes_count: c.likes_count ?? 0, dislikes_count: c.dislikes_count ?? 0,
    user_reaction: c.user_reaction ?? null, reported: false,
    replies: (c.replies||[]).map(r => normalize(r)),
})

// ── Load ──────────────────────────────────────────────────────────────────────
const loadComments = async () => {
    loading.value = true
    try {
        const d = await apiGet('/api/comments?page=1')
        comments.value   = (d.data||[]).map(normalize)
        currentPage.value = d.current_page ?? 1
        lastPage.value    = d.last_page    ?? 1
    } catch(e){ console.error(e) } finally { loading.value = false }
}

const loadMore = async () => {
    if (loadingMore.value || !hasMore.value) return
    loadingMore.value = true
    try {
        const d = await apiGet(`/api/comments?page=${currentPage.value+1}`)
        comments.value.push(...(d.data||[]).map(normalize))
        currentPage.value = d.current_page; lastPage.value = d.last_page
    } finally { loadingMore.value = false }
}

const toggleWall = async () => {
    isOpen.value = !isOpen.value; unreadNew.value = 0
    if (isOpen.value && !comments.value.length) await loadComments()
}

// ── Submit ────────────────────────────────────────────────────────────────────
const submitComment = async () => {
    const body = newBody.value.trim()
    if (!body || sending.value) return
    postError.value = ''; sending.value = true
    try {
        const d = await apiPost('/api/comments', { body, parent_id: replyingTo.value?.id ?? null })
        if (d.success) {
            const c = normalize(d.comment)
            if (c.parent_id) {
                const p = comments.value.find(x => x.id === c.parent_id)
                if (p) p.replies.push(c)
            } else {
                comments.value.unshift({ ...c, replies: [] })
            }
            newBody.value = ''; replyingTo.value = null
            if (mainTextarea.value) mainTextarea.value.style.height = 'auto'
            await nextTick()
            if (!c.parent_id) feedEl.value?.scrollTo({ top: 0, behavior: 'smooth' })
        }
    } catch(e) {
        postError.value = e.message === 'SESSION_EXPIRED'
            ? 'Session expirée. Veuillez recharger la page.'
            : (e.message || 'Erreur lors de l\'envoi.')
    } finally { sending.value = false }
}

// ── React ─────────────────────────────────────────────────────────────────────
const react = async (comment, value) => {
    try {
        const d = await apiPost(`/api/comments/${comment.id}/react`, { value })
        if (d.success) {
            comment.likes_count = d.likes_count; comment.dislikes_count = d.dislikes_count
            comment.user_reaction = d.user_reaction
        }
    } catch(e){ console.error(e) }
}

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteComment = async (comment, parent) => {
    if (!confirm('Supprimer ce commentaire ?')) return
    try {
        const d = await apiDelete(`/api/comments/${comment.id}`)
        if (d.success) {
            if (parent) { parent.replies = parent.replies.filter(r => r.id !== comment.id) }
            else { const i = comments.value.findIndex(c => c.id === comment.id); if (i !== -1) comments.value.splice(i, 1) }
        }
    } catch(e){ console.error(e) }
}

// ── Report ────────────────────────────────────────────────────────────────────
const reportComment = async (comment) => {
    if (comment.reported) return
    const reason = prompt('Raison du signalement (optionnel) :')
    if (reason === null) return
    try {
        const d = await apiPost(`/api/comments/${comment.id}/report`, { reason })
        if (d.success) comment.reported = true
        else alert(d.message || 'Déjà signalé')
    } catch(e){ console.error(e) }
}

// ── Reply ─────────────────────────────────────────────────────────────────────
const startReply = (c) => {
    replyingTo.value = c; newBody.value = `@${c.user_name} `
    nextTick(() => mainTextarea.value?.focus())
}
const cancelReply = () => { replyingTo.value = null; newBody.value = '' }
const autoResize  = (e) => {
    e.target.style.height = 'auto'
    e.target.style.height = Math.min(e.target.scrollHeight, 110) + 'px'
}
</script>

<style scoped>
.cw-root {
    --cp:#2563eb; --cpd:#1e40af; --cpl:#eff6ff;
    --cs:#fff; --cs2:#f8fafc; --cs3:#f1f5f9;
    --cb:#e2e8f0; --cb2:#cbd5e1;
    --ct:#0f172a; --ct2:#334155; --ct3:#64748b; --ct4:#94a3b8;
    --ok:#16a34a; --warn:#d97706; --bad:#dc2626;
    --r:16px;
    --shadow: 0 20px 60px rgba(15,23,42,.14), 0 8px 24px rgba(15,23,42,.08);
    position:fixed; bottom:6.2rem; right:1.5rem; z-index:9998;
    font-family:'Segoe UI','SF Pro Text',system-ui,sans-serif; font-size:13.5px; color:var(--ct);
}
:global(.dark) .cw-root {
    --cs:#1e293b; --cs2:#0f172a; --cs3:#1e293b;
    --cb:#334155; --cb2:#475569;
    --ct:#f1f5f9; --ct2:#e2e8f0; --ct3:#94a3b8; --ct4:#64748b; --cpl:#1e3a5f;
}
/* FAB */
.cw-fab {
    position:relative; width:52px; height:52px; border-radius:50%;
    background:var(--cp); color:#fff; border:none; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 4px 18px rgba(37,99,235,.4),0 1px 4px rgba(0,0,0,.12);
    transition:transform .18s,box-shadow .18s,background .15s;
}
.cw-fab svg{width:24px;height:24px}
.cw-fab:hover{transform:scale(1.08);box-shadow:0 6px 26px rgba(37,99,235,.5)}
.cw-fab--open{background:#ef4444;box-shadow:0 4px 18px rgba(239,68,68,.4)}
.cw-fab__badge{
    position:absolute;top:-4px;right:-4px;
    background:#f59e0b;color:#fff;font-size:10px;font-weight:700;
    min-width:18px;height:18px;border-radius:9px;padding:0 4px;
    display:flex;align-items:center;justify-content:center;
    animation:cw-pulse 1.6s ease-in-out infinite;
}
/* Panel */
.cw-panel {
    position:absolute; bottom:62px; right:0;
    width:min(480px,calc(100vw - 2rem));
    height:min(600px,calc(100vh - 5.5rem));
    background:var(--cs); border-radius:var(--r);
    box-shadow:var(--shadow); border:1px solid var(--cb);
    display:flex; flex-direction:column; overflow:hidden;
    transform-origin:bottom right;
}
/* Header */
.cw-header{
    display:flex;align-items:center;justify-content:space-between;
    padding:13px 15px; background:var(--cs); border-bottom:1px solid var(--cb); flex-shrink:0;
}
.cw-header__left{display:flex;align-items:center;gap:10px}
.cw-header__icon-wrap{
    width:34px;height:34px;border-radius:10px;
    background:var(--cpl);color:var(--cp);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.cw-header__title{font-size:.87rem;font-weight:700;margin:0;color:var(--ct);letter-spacing:-.01em}
.cw-header__sub{font-size:.63rem;color:var(--ct4);margin:1px 0 0}
.cw-close-btn{
    width:28px;height:28px;border-radius:8px;
    background:var(--cs3);border:1px solid var(--cb);color:var(--ct3);
    cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .12s;
}
.cw-close-btn:hover{background:#fee2e2;color:var(--bad);border-color:#fecaca}
/* Compose */
.cw-compose{
    display:flex;gap:10px;padding:12px 14px;
    border-bottom:1px solid var(--cb);background:var(--cs);flex-shrink:0;
}
.cw-av{
    width:32px;height:32px;min-width:32px;border-radius:50%;
    color:#fff;font-size:.73rem;font-weight:700;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.cw-compose__inner{flex:1;min-width:0;display:flex;flex-direction:column;gap:6px}
.cw-reply-tag{
    display:inline-flex;align-items:center;gap:5px;
    background:var(--cpl);color:var(--cp);
    border:1px solid #bfdbfe;border-radius:20px;
    font-size:.64rem;font-weight:600;padding:3px 9px;width:fit-content;
}
.cw-reply-tag__x{background:none;border:none;cursor:pointer;color:var(--cp);font-size:.62rem;padding:0 1px;line-height:1}
.cw-textarea{
    width:100%;border:1.5px solid var(--cb);border-radius:10px;padding:8px 11px;
    font-size:.79rem;color:var(--ct);background:var(--cs2);
    resize:none;outline:none;font-family:inherit;line-height:1.5;
    min-height:36px;max-height:110px;box-sizing:border-box;
    transition:border-color .15s,box-shadow .15s;
}
.cw-textarea:focus{border-color:var(--cp);box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.cw-textarea::placeholder{color:var(--ct4)}
.cw-compose__bar{display:flex;align-items:center;gap:8px}
.cw-char{font-size:.61rem;color:var(--ct4)}
.cw-char--warn{color:var(--warn);font-weight:600}
.cw-hint{font-size:.6rem;color:var(--ct4);margin-right:auto}
.cw-post-btn{
    display:flex;align-items:center;gap:5px;
    padding:5px 14px;border-radius:20px;
    background:var(--cp);color:#fff;border:none;
    font-size:.74rem;font-weight:600;cursor:pointer;
    transition:background .15s,transform .12s;
}
.cw-post-btn:hover:not(:disabled){background:var(--cpd);transform:translateY(-1px)}
.cw-post-btn:disabled{opacity:.45;cursor:default;transform:none}
.cw-err{font-size:.67rem;color:var(--bad);margin:0}
/* Feed */
.cw-feed{
    flex:1;overflow-y:auto;padding:10px 12px;
    background:var(--cs2);scroll-behavior:smooth;
}
.cw-feed::-webkit-scrollbar{width:4px}
.cw-feed::-webkit-scrollbar-track{background:transparent}
.cw-feed::-webkit-scrollbar-thumb{background:var(--cb2);border-radius:2px}
.cw-center{display:flex;align-items:center;justify-content:center;height:100%}
.cw-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:6px;text-align:center}
.cw-empty__t{font-size:.83rem;font-weight:600;color:var(--ct3);margin:0}
.cw-empty__s{font-size:.71rem;color:var(--ct4);margin:0;max-width:200px}
/* List + Cards */
.cw-list{display:flex;flex-direction:column;gap:8px}
.cw-card{
    background:var(--cs);border-radius:12px;border:1px solid var(--cb);
    overflow:hidden;transition:box-shadow .15s;
}
.cw-card:hover{box-shadow:0 2px 12px rgba(15,23,42,.07)}
.cw-item{display:flex;gap:9px;padding:10px 12px}
.cw-item--reply{background:var(--cs2);padding:8px 12px}
.cw-replies .cw-item+.cw-item{border-top:1px solid var(--cb)}
.cw-item__av{
    width:30px;height:30px;min-width:30px;border-radius:50%;
    color:#fff;font-size:.7rem;font-weight:700;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.cw-item__av--sm{width:24px;height:24px;min-width:24px;font-size:.62rem}
.cw-item__body{flex:1;min-width:0}
.cw-item__meta{display:flex;align-items:center;gap:5px;margin-bottom:3px;flex-wrap:wrap}
.cw-item__name{font-size:.77rem;font-weight:700;color:var(--ct)}
.cw-item__you{
    font-size:.57rem;padding:1px 5px;border-radius:5px;
    background:var(--cpl);color:var(--cp);font-weight:700;
}
.cw-item__time{font-size:.61rem;color:var(--ct4);margin-left:2px}
.cw-item__flag{font-size:.65rem;color:var(--bad);margin-left:auto}
.cw-item__text{
    font-size:.79rem;color:var(--ct2);line-height:1.58;
    margin:0 0 6px;word-break:break-word;white-space:pre-line;
}
:global(.cw-mention){color:var(--cp);font-weight:600}
/* Actions */
.cw-item__actions{display:flex;align-items:center;gap:2px;flex-wrap:wrap}
.cw-btn{
    display:inline-flex;align-items:center;gap:4px;
    padding:4px 8px;border-radius:20px;
    background:transparent;border:1px solid transparent;
    font-size:.67rem;font-weight:500;color:var(--ct3);
    cursor:pointer;transition:all .12s;line-height:1;
}
.cw-btn span{line-height:1}
.cw-btn:hover{background:var(--cs3);color:var(--ct2);border-color:var(--cb)}
.cw-btn--liked{color:var(--ok);background:#dcfce7;border-color:#bbf7d0;font-weight:700}
.cw-btn--disliked{color:var(--bad);background:#fee2e2;border-color:#fecaca;font-weight:700}
.cw-btn--report{color:var(--warn)}
.cw-btn--report:hover{background:#fef3c7;border-color:#fde68a;color:#92400e}
.cw-btn--reported{color:var(--bad);background:#fee2e2;border-color:#fecaca;cursor:default}
.cw-btn--del{color:var(--ct4);margin-left:auto}
.cw-btn--del:hover{color:var(--bad);background:#fee2e2;border-color:#fecaca}
/* Replies section */
.cw-replies{border-top:1px solid var(--cb);background:var(--cs2);display:flex;flex-direction:column}
/* Load more */
.cw-more{display:flex;justify-content:center;padding:10px 0 4px}
.cw-more-btn{
    padding:6px 20px;border-radius:20px;
    background:var(--cs);border:1px solid var(--cb);
    font-size:.72rem;color:var(--ct3);cursor:pointer;
    transition:all .13s;display:flex;align-items:center;gap:6px;
}
.cw-more-btn:hover{border-color:var(--cp);color:var(--cp);background:var(--cpl)}
/* Spinners */
.cw-spinner{width:24px;height:24px;border-radius:50%;border:2.5px solid var(--cb);border-top-color:var(--cp);animation:cw-spin .7s linear infinite}
.cw-spin-xs{width:11px;height:11px;border-radius:50%;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;animation:cw-spin .7s linear infinite;display:inline-block}
.cw-spin-xs--dark{border-color:var(--cb);border-top-color:var(--cp)}
@keyframes cw-spin{to{transform:rotate(360deg)}}
@keyframes cw-pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.2)}}
/* Transitions */
.wall-pop-enter-active{animation:pop-in .22s cubic-bezier(.34,1.5,.64,1)}
.wall-pop-leave-active{animation:pop-in .14s ease-in reverse}
@keyframes pop-in{from{opacity:0;transform:scale(.9) translateY(8px)}to{opacity:1;transform:scale(1) translateY(0)}}
.icon-swap-enter-active,.icon-swap-leave-active{transition:opacity .12s,transform .12s}
.icon-swap-enter-from{opacity:0;transform:rotate(-45deg) scale(.5)}
.icon-swap-leave-to{opacity:0;transform:rotate(45deg) scale(.5)}
.reply-slide-enter-active{animation:rslide .15s ease}
.reply-slide-leave-active{animation:rslide .12s ease reverse}
@keyframes rslide{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:none}}
@media(max-width:520px){.cw-panel{width:calc(100vw - 1.5rem)}}
</style>