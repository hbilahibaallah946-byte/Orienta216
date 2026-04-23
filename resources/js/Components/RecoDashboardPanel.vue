<template>
    <div class="reco-panel">
        <div v-if="loading" class="reco-panel__loading">
            <div class="spinner"></div>
            <span>Chargement…</span>
        </div>
        <template v-else-if="data">
            <div v-if="data.contexte_academique" class="reco-panel__ctx">
                <p class="reco-panel__ctx-title">Profil académique &amp; score T (app)</p>
                <ul class="reco-panel__ctx-list">
                    <li v-if="data.contexte_academique.serie_bac_label">
                        Série / filière bac : <strong>{{ data.contexte_academique.serie_bac_label }}</strong>
                    </li>
                    <li v-if="data.contexte_academique.moyenne_generale != null">
                        Moyenne générale : <strong>{{ data.contexte_academique.moyenne_generale }}/20</strong>
                    </li>
                    <li v-if="data.contexte_academique.score_orientation_T != null">
                        Score orientation (×2, formule simplifiée) : <strong>{{ data.contexte_academique.score_orientation_T }}</strong>
                    </li>
                    <li v-if="data.contexte_academique.score_orientation_T_plus_7 != null">
                        Score +7&nbsp;% : <strong>{{ data.contexte_academique.score_orientation_T_plus_7 }}</strong>
                    </li>
                    <li v-if="!data.contexte_academique.a_moyenne" class="reco-panel__warn">
                        Aucune moyenne enregistrée : les scores académique et compétitivité utilisent des valeurs neutres (50&nbsp;%).
                    </li>
                </ul>
                <p v-if="data.contexte_academique.poids" class="reco-panel__poids">
                    Pondération finale :
                    académique {{ (data.contexte_academique.poids.academique * 100).toFixed(0) }}&nbsp;%,
                    compatibilité {{ (data.contexte_academique.poids.compatibilite * 100).toFixed(0) }}&nbsp;%,
                    compétitivité {{ (data.contexte_academique.poids.competitivite * 100).toFixed(0) }}&nbsp;%
                </p>
            </div>

            <div v-if="!recoList.length" class="reco-panel__empty">
                <span>📝</span>
                <p>{{ emptyMessage }}</p>
            </div>

            <div v-else class="reco-table-wrap">
                <table class="reco-table">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Filière</th>
                            <th>Final</th>
                            <th>Acad.</th>
                            <th>Compat.</th>
                            <th>Compét.</th>
                            <th>Score +7%</th>
                            <th>Bac</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in recoList" :key="r.rang">
                            <td>{{ r.rang }}</td>
                            <td class="reco-table__name">
                                <span class="reco-table__nom">{{ r.filiere?.nom }}</span>
                                <span v-if="r.filiere?.universite" class="reco-table__uni">{{ r.filiere.universite }}</span>
                            </td>
                            <td><span class="reco-badge" :class="'reco-badge--' + tier(r.score)">{{ r.score }}%</span></td>
                            <td>{{ fmtScore(getDetailScore(r, 'academique')) }}</td>
                            <td>{{ fmtScore(getDetailScore(r, 'compatibilite')) }}</td>
                            <td>{{ fmtScore(getDetailScore(r, 'competitivite')) }}</td>
                            <td>
                                <span v-if="r.bonus_7?.recommande_avec_bonus" class="reco-badge reco-badge--high">
                                    Très recommandé (+7%)
                                </span>
                                <span v-else-if="r.bonus_7?.proche" class="reco-badge reco-badge--mid">
                                    Proche du seuil (+7%)
                                </span>
                                <span v-else>—</span>
                            </td>
                            <td>{{ bacLabel(r.accessible_selon_bac) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
        <div v-else class="reco-panel__empty">
            <p>Aucune donnée.</p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    data: { type: Object, default: null },
    loading: { type: Boolean, default: false },
})

const recoList = computed(() => props.data?.recommandations ?? [])
const recoState = computed(() => props.data?.etat_recommandation ?? null)
const emptyMessage = computed(() => {
    switch (recoState.value) {
    case 'profil_incomplet':
        return 'Pas encore de recommandations. Remplissez le questionnaire pour lancer le matching.'
    case 'aucune_filiere':
        return 'Aucune filière disponible pour le moment. Ajoutez des filières pour générer un classement.'
    case 'aucune_filiere_configuree':
        return 'Les recommandations sont en mode basique: configurez les critères ou importez les seuils ministère pour améliorer le classement.'
    case 'aucune_recommandation':
        return 'Le calcul n’a pas encore produit de recommandations. Essayez un recalcul du profil.'
    default:
        return 'Pas encore de recommandations. Remplir le questionnaire et importer les seuils ministère sur les filières améliore le classement.'
    }
})

function tier(score) {
    if (score >= 80) return 'high'
    if (score >= 60) return 'mid'
    return 'low'
}

function fmtScore(v) {
    if (v == null || v === '') return '—'
    return `${v}%`
}

function getDetailScore(r, key) {
    const direct = r?.scores_detail?.[key]
    if (direct != null && direct !== '') return direct
    const fallbackMap = {
        academique: 'score_academique',
        compatibilite: 'score_compatibilite',
        competitivite: 'score_competitivite',
    }
    const field = fallbackMap[key]
    return field ? r?.[field] : null
}

function bacLabel(ok) {
    if (ok == null) return '—'
    return ok ? 'OK' : 'À vérifier'
}
</script>

<style scoped>
.reco-panel { font-size: 0.72rem; color: var(--ct, #0f172a); }
:global(.dark) .reco-panel { color: var(--ct, #f1f5f9); }

.reco-panel__loading {
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
    padding: 2rem; color: var(--ct3, #94a3b8);
}

.reco-panel__ctx {
    background: var(--cs, #fff);
    border: 1px solid var(--cb, #e2e8f0);
    border-radius: 10px;
    padding: 0.55rem 0.65rem;
    margin-bottom: 0.5rem;
}
:global(.dark) .reco-panel__ctx {
    background: var(--cs, #1e293b);
    border-color: var(--cb, #334155);
}

.reco-panel__ctx-title {
    font-weight: 700;
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--ct2, #64748b);
    margin: 0 0 0.35rem 0;
}

.reco-panel__ctx-list {
    margin: 0;
    padding-left: 1rem;
    line-height: 1.45;
}

.reco-panel__warn {
    color: #b45309;
    list-style: none;
    margin-left: -1rem;
}
:global(.dark) .reco-panel__warn { color: #fbbf24; }

.reco-panel__poids {
    margin: 0.4rem 0 0 0;
    font-size: 0.65rem;
    color: var(--ct2, #64748b);
}

.reco-panel__empty {
    text-align: center;
    padding: 1.5rem 0.75rem;
    color: var(--ct3, #94a3b8);
}
.reco-panel__empty span { font-size: 1.75rem; }

.reco-table-wrap {
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid var(--cb, #e2e8f0);
}
:global(.dark) .reco-table-wrap { border-color: #334155; }

.reco-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.68rem;
    background: var(--cs, #fff);
}
:global(.dark) .reco-table { background: #1e293b; }

.reco-table th,
.reco-table td {
    padding: 0.35rem 0.4rem;
    text-align: left;
    border-bottom: 1px solid var(--cb, #e2e8f0);
    vertical-align: top;
}
:global(.dark) .reco-table th,
:global(.dark) .reco-table td { border-color: #334155; }

.reco-table th {
    background: var(--cs2, #f8fafc);
    font-weight: 700;
    color: var(--ct2, #64748b);
    white-space: nowrap;
}
:global(.dark) .reco-table th { background: #0f172a; }

.reco-table__name { max-width: 9rem; }
.reco-table__nom { display: block; font-weight: 600; color: var(--ct, #0f172a); }
:global(.dark) .reco-table__nom { color: #f1f5f9; }
.reco-table__uni { display: block; font-size: 0.62rem; color: var(--ct3, #94a3b8); margin-top: 0.1rem; }

.reco-badge {
    display: inline-block;
    font-weight: 700;
    padding: 0.1rem 0.35rem;
    border-radius: 6px;
}
.reco-badge--high { background: #dcfce7; color: #166534; }
.reco-badge--mid { background: #fef9c3; color: #854d0e; }
.reco-badge--low { background: #fee2e2; color: #991b1b; }
:global(.dark) .reco-badge--high { background: #14532d; color: #86efac; }
:global(.dark) .reco-badge--mid { background: #713f12; color: #fde047; }
:global(.dark) .reco-badge--low { background: #7f1d1d; color: #fca5a5; }

.spinner {
    width: 22px; height: 22px;
    border: 2px solid var(--cb, #e2e8f0);
    border-top-color: var(--cp, #4f46e5);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
