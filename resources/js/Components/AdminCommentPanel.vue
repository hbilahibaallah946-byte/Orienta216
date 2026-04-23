<template>
    <div class="ac-root">
        <!-- FAB -->
        <button @click="toggle" class="ac-fab" :class="{ 'ac-fab--open': isOpen }" title="Modération des commentaires">
            <transition name="icon-swap" mode="out-in">
                <span v-if="!isOpen" key="o" class="ac-fab__inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-4 4v-4z"/>
                    </svg>
                    <span v-if="reportedCount > 0" class="ac-fab__badge">{{ reportedCount > 9 ? '9+' : reportedCount }}</span>
                </span>
                <svg v-else key="c" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:20px;height:20px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </transition>
        </button>

        <!-- Panel -->
        <transition name="ac-pop">
            <div v-if="isOpen" class="ac-panel">

                <!-- Header -->
                <div class="ac-header">
                    <div class="ac-header__left">
                        <div class="ac-header__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="ac-header__title">Modération</h3>
                            <p class="ac-header__sub">{{ allCount }} commentaires • {{ reportedCount }} signalé{{ reportedCount!==1?'s':'' }}</p>
                        </div>
                    </div>
                    <button @click="isOpen = false" class="ac-close-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Filter tabs -->
                <div class="ac-tabs">
                    <button
                        v-for="tab in tabs" :key="tab.id"
                        @click="activeTab = tab.id; loadComments()"
                        class="ac-tab"
                        :class="{ 'ac-tab--on': activeTab === tab.id }"
                    >
                        {{ tab.label }}
                        <span v-if="tab.count > 0" class="ac-tab__count" :class="tab.id === 'reported' && 'ac-tab__count--red'">{{ tab.count }}</span>
                    </button>
                </div>

                <!-- Feed -->
                <div class="ac-feed" ref="feedEl">
                    <div v-if="loading" class="ac-center"><div class="ac-spinner"></div></div>

                    <div v-else-if="!visibleComments.length" class="ac-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" style="width:36px;height:36px;opacity:.25"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p>Aucun commentaire {{ activeTab === 'reported' ? 'signalé' : '' }}</p>
                    </div>

                    <div v-else class="ac-list">
                        <div v-for="c in visibleComments" :key="c.id" class="ac-card" :class="{ 'ac-card--flagged': c.reports_count > 0 }">
                            <div class="ac-card__top">
                                <div class="ac-av" :style="{ background: avatarColor(c.user_name) }">{{ initial(c.user_name) }}</div>
                                <div class="ac-card__meta">
                                    <span class="ac-card__author">{{ c.user_name }}</span>
                                    <span class="ac-card__time">{{ fmtTime(c.created_at) }}</span>
                                    <span v-if="c.reports_count > 0" class="ac-card__reports">
                                        <svg viewBox="0 0 24 24" fill="currentColor" style="width:11px;height:11px"><path d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                        {{ c.reports_count }} signalement{{ c.reports_count!==1?'s':'' }}
                                    </span>
                                </div>
                                <button @click="deleteComment(c)" class="ac-del-btn" title="Supprimer">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Supprimer
                                </button>
                            </div>
                            <p class="ac-card__text" v-html="highlightMentions(c.body)"></p>
                            <div class="ac-card__stats">
                                <span class="ac-stat ac-stat--like">
                                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:11px;height:11px"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
                                    {{ c.likes_count }}
                                </span>
                                <span class="ac-stat ac-stat--dislike">
                                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:11px;height:11px"><path d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.105-1.79l-.05-.025A4 4 0 0011.055 2H5.64a2 2 0 00-1.962 1.608l-1.2 6A2 2 0 004.44 12H8v4a2 2 0 002 2 1 1 0 001-1v-.667a4 4 0 01.8-2.4l1.4-1.866a4 4 0 00.8-2.4z"/></svg>
                                    {{ c.dislikes_count }}
                                </span>
                                <span v-if="c.replies_count > 0" class="ac-stat">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    {{ c.replies_count }} réponse{{ c.replies_count!==1?'s':'' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="hasMore" class="ac-more">
                        <button @click="loadMore" :disabled="loadingMore" class="ac-more-btn">
                            <span v-if="loadingMore" class="ac-spin-xs"></span>
                            <span v-else>Voir plus</span>
                        </button>
                    </div>
                </div>

            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const isOpen      = ref(false)
const comments    = ref([])
const loading     = ref(false)
const loadingMore = ref(false)
const activeTab   = ref('all')
const currentPage = ref(1)
const lastPage    = ref(1)
const feedEl      = ref(null)

const allCount      = computed(() => comments.value.length)
const reportedCount = computed(() => comments.value.filter(c => c.reports_count > 0).length)
const hasMore       = computed(() => currentPage.value < lastPage.value)

const visibleComments = computed(() => {
    if (activeTab.value === 'reported') return comments.value.filter(c => c.reports_count > 0)
    return comments.value
})

const tabs = computed(() => [
    { id: 'all',      label: 'Tous',      count: allCount.value },
    { id: 'reported', label: '⚑ Signalés', count: reportedCount.value },
])

// CSRF
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
    'Content-Type': 'application/json', Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': getCsrf(), 'X-XSRF-TOKEN': getCsrf(),
})
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

// Helpers
const PALETTE = ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#6366f1']
const avatarColor = (n) => PALETTE[[...(n||'')].reduce((a,c)=>a+c.charCodeAt(0),0) % PALETTE.length]
const initial     = (n) => (n||'?').charAt(0).toUpperCase()
const fmtTime = (d) => {
    if (!d) return ''
    const s = Math.floor((Date.now() - new Date(d)) / 1000)
    if (s < 60) return 'À l\'instant'
    if (s < 3600) return `${Math.floor(s/60)} min`
    if (s < 86400) return `${Math.floor(s/3600)}h`
    return new Date(d).toLocaleDateString('fr-FR', { day:'2-digit', month:'short', year:'numeric' })
}
const highlightMentions = (t) => (t||'').replace(/@(\S+)/g, '<span style="color:#2563eb;font-weight:600">@$1</span>')

// Load
const normalize = (c) => ({
    id: c.id, body: c.body, user_id: c.user_id,
    user_name: c.user?.name ?? c.user_name ?? '?',
    created_at: c.created_at,
    likes_count: c.likes_count ?? 0, dislikes_count: c.dislikes_count ?? 0,
    reports_count: c.reports?.length ?? c.reports_count ?? 0,
    replies_count: c.replies?.length ?? c.replies_count ?? 0,
})

const loadComments = async () => {
    loading.value = true; comments.value = []
    try {
        const d = await apiGet('/api/comments?page=1&per_page=50')
        comments.value   = (d.data||[]).map(normalize)
        currentPage.value = d.current_page ?? 1
        lastPage.value    = d.last_page    ?? 1
    } catch(e) { console.error(e) } finally { loading.value = false }
}

const loadMore = async () => {
    if (loadingMore.value || !hasMore.value) return
    loadingMore.value = true
    try {
        const d = await apiGet(`/api/comments?page=${currentPage.value+1}&per_page=50`)
        comments.value.push(...(d.data||[]).map(normalize))
        currentPage.value = d.current_page; lastPage.value = d.last_page
    } finally { loadingMore.value = false }
}

const toggle = async () => {
    isOpen.value = !isOpen.value
    if (isOpen.value && !comments.value.length) await loadComments()
}

const deleteComment = async (c) => {
    if (!confirm(`Supprimer le commentaire de ${c.user_name} ?`)) return
    try {
        const d = await apiDelete(`/api/comments/${c.id}`)
        if (d.success) {
            const i = comments.value.findIndex(x => x.id === c.id)
            if (i !== -1) comments.value.splice(i, 1)
        }
    } catch(e) { alert('Erreur: ' + e.message) }
}

onMounted(() => {
    // Refresh report count every 60s
    setInterval(async () => {
        if (!isOpen.value) {
            try {
                const d = await apiGet('/api/comments?page=1&per_page=50')
                const loaded = (d.data||[]).map(normalize)
                // just update report counts silently
                loaded.forEach(nc => {
                    const ex = comments.value.find(c => c.id === nc.id)
                    if (ex) ex.reports_count = nc.reports_count
                })
            } catch {}
        }
    }, 60000)
})
</script>

<style scoped>
.ac-root {
    --cp:#7c3aed; --cpd:#6d28d9; --cpl:#f5f3ff;
    --cs:#fff; --cs2:#f8fafc; --cs3:#f1f5f9;
    --cb:#e2e8f0; --cb2:#cbd5e1;
    --ct:#0f172a; --ct2:#334155; --ct3:#64748b; --ct4:#94a3b8;
    --ok:#16a34a; --warn:#d97706; --bad:#dc2626;
    --shadow:0 20px 60px rgba(15,23,42,.14),0 8px 24px rgba(15,23,42,.08);
    position:fixed; bottom:1.5rem; right:1.5rem; z-index:9997;
    font-family:'Segoe UI','SF Pro Text',system-ui,sans-serif; font-size:13.5px;
}
:global(.dark) .ac-root {
    --cs:#1e293b; --cs2:#0f172a; --cs3:#1e293b;
    --cb:#334155; --cb2:#475569;
    --ct:#f1f5f9; --ct2:#e2e8f0; --ct3:#94a3b8; --ct4:#64748b; --cpl:#2e1065;
}
/* FAB */
.ac-fab{
    width:52px;height:52px;border-radius:50%;
    background:var(--cp);color:#fff;border:none;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 4px 18px rgba(124,58,237,.4);
    transition:transform .18s,box-shadow .18s,background .15s;
}
.ac-fab:hover{transform:scale(1.08);box-shadow:0 6px 26px rgba(124,58,237,.5)}
.ac-fab--open{background:#ef4444;box-shadow:0 4px 18px rgba(239,68,68,.4)}
.ac-fab__inner{position:relative;display:flex;align-items:center;justify-content:center}
.ac-fab__badge{
    position:absolute;top:-10px;right:-10px;
    background:#ef4444;color:#fff;font-size:10px;font-weight:700;
    min-width:18px;height:18px;border-radius:9px;padding:0 4px;
    display:flex;align-items:center;justify-content:center;
    animation:ac-pulse 1.6s ease-in-out infinite;
}
/* Panel */
.ac-panel{
    position:absolute;bottom:62px;right:0;
    width:min(500px,calc(100vw - 2rem));
    height:min(580px,calc(100vh - 5.5rem));
    background:var(--cs);border-radius:16px;
    box-shadow:var(--shadow);border:1px solid var(--cb);
    display:flex;flex-direction:column;overflow:hidden;
    transform-origin:bottom right;
}
/* Header */
.ac-header{
    display:flex;align-items:center;justify-content:space-between;
    padding:13px 15px;background:var(--cs);border-bottom:1px solid var(--cb);flex-shrink:0;
}
.ac-header__left{display:flex;align-items:center;gap:10px}
.ac-header__icon{
    width:34px;height:34px;border-radius:10px;
    background:var(--cpl);color:var(--cp);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.ac-header__title{font-size:.87rem;font-weight:700;margin:0;color:var(--ct);letter-spacing:-.01em}
.ac-header__sub{font-size:.63rem;color:var(--ct4);margin:1px 0 0}
.ac-close-btn{
    width:28px;height:28px;border-radius:8px;
    background:var(--cs3);border:1px solid var(--cb);color:var(--ct3);
    cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .12s;
}
.ac-close-btn:hover{background:#fee2e2;color:var(--bad);border-color:#fecaca}
/* Tabs */
.ac-tabs{
    display:flex;border-bottom:1px solid var(--cb);background:var(--cs);flex-shrink:0;padding:0 12px;gap:2px;
}
.ac-tab{
    padding:9px 12px;border:none;background:none;cursor:pointer;
    font-size:.73rem;font-weight:600;color:var(--ct3);
    border-bottom:2px solid transparent;margin-bottom:-1px;
    transition:color .12s,border-color .12s;
    display:flex;align-items:center;gap:5px;
}
.ac-tab:hover{color:var(--cp)}
.ac-tab--on{color:var(--cp);border-bottom-color:var(--cp)}
.ac-tab__count{
    padding:1px 6px;border-radius:10px;
    background:var(--cs3);color:var(--ct3);font-size:.62rem;
}
.ac-tab__count--red{background:#fee2e2;color:var(--bad)}
/* Feed */
.ac-feed{flex:1;overflow-y:auto;padding:10px 12px;background:var(--cs2)}
.ac-feed::-webkit-scrollbar{width:4px}
.ac-feed::-webkit-scrollbar-thumb{background:var(--cb2);border-radius:2px}
.ac-center{display:flex;align-items:center;justify-content:center;height:100%}
.ac-empty{
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    height:100%;gap:8px;color:var(--ct3);font-size:.8rem;text-align:center;
}
/* List */
.ac-list{display:flex;flex-direction:column;gap:8px}
.ac-card{
    background:var(--cs);border:1px solid var(--cb);border-radius:12px;
    padding:10px 12px;transition:box-shadow .15s;
}
.ac-card:hover{box-shadow:0 2px 12px rgba(15,23,42,.07)}
.ac-card--flagged{border-left:3px solid var(--bad);background:#fff8f8}
:global(.dark) .ac-card--flagged{background:#1e0a0a}
.ac-card__top{display:flex;align-items:center;gap:8px;margin-bottom:6px}
.ac-av{
    width:28px;height:28px;min-width:28px;border-radius:50%;
    color:#fff;font-size:.67rem;font-weight:700;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.ac-card__meta{display:flex;align-items:center;gap:6px;flex:1;min-width:0;flex-wrap:wrap}
.ac-card__author{font-size:.76rem;font-weight:700;color:var(--ct)}
.ac-card__time{font-size:.61rem;color:var(--ct4)}
.ac-card__reports{
    display:inline-flex;align-items:center;gap:3px;
    font-size:.62rem;font-weight:700;color:var(--bad);
    background:#fee2e2;border:1px solid #fecaca;
    border-radius:10px;padding:1px 7px;
}
.ac-del-btn{
    display:flex;align-items:center;gap:4px;
    padding:4px 10px;border-radius:8px;
    background:#fee2e2;border:1px solid #fecaca;
    color:var(--bad);font-size:.68rem;font-weight:600;cursor:pointer;
    transition:all .12s;margin-left:auto;flex-shrink:0;
}
.ac-del-btn:hover{background:var(--bad);color:#fff;border-color:var(--bad)}
.ac-card__text{
    font-size:.78rem;color:var(--ct2);line-height:1.56;
    margin:0 0 7px;word-break:break-word;white-space:pre-line;
}
.ac-card__stats{display:flex;align-items:center;gap:8px}
.ac-stat{
    display:inline-flex;align-items:center;gap:3px;
    font-size:.63rem;color:var(--ct4);
}
.ac-stat--like{color:var(--ok)}
.ac-stat--dislike{color:var(--bad)}
/* More */
.ac-more{display:flex;justify-content:center;padding:10px 0 4px}
.ac-more-btn{
    padding:6px 20px;border-radius:20px;
    background:var(--cs);border:1px solid var(--cb);
    font-size:.72rem;color:var(--ct3);cursor:pointer;
    transition:all .13s;display:flex;align-items:center;gap:6px;
}
.ac-more-btn:hover{border-color:var(--cp);color:var(--cp);background:var(--cpl)}
/* Spinner */
.ac-spinner{width:24px;height:24px;border-radius:50%;border:2.5px solid var(--cb);border-top-color:var(--cp);animation:ac-spin .7s linear infinite}
.ac-spin-xs{width:11px;height:11px;border-radius:50%;border:2px solid var(--cb);border-top-color:var(--cp);animation:ac-spin .7s linear infinite;display:inline-block}
@keyframes ac-spin{to{transform:rotate(360deg)}}
@keyframes ac-pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.2)}}
/* Transitions */
.ac-pop-enter-active{animation:ac-in .22s cubic-bezier(.34,1.5,.64,1)}
.ac-pop-leave-active{animation:ac-in .14s ease-in reverse}
@keyframes ac-in{from{opacity:0;transform:scale(.9) translateY(8px)}to{opacity:1;transform:scale(1) translateY(0)}}
.icon-swap-enter-active,.icon-swap-leave-active{transition:opacity .12s,transform .12s}
.icon-swap-enter-from{opacity:0;transform:rotate(-45deg) scale(.5)}
.icon-swap-leave-to{opacity:0;transform:rotate(45deg) scale(.5)}
@media(max-width:520px){.ac-panel{width:calc(100vw - 1.5rem)}}
</style>