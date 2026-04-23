<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-6">

                <!-- Header -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📊 Statistiques Plateforme</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Vue d'ensemble de tous les utilisateurs</p>
                </div>

                <!-- ── KPI Cards ──────────────────────────────────────────── -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                    <div class="bg-gradient-to-br from-indigo-600 to-indigo-500 rounded-2xl p-5 text-white shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl">🎓</span>
                            <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full font-medium">
                                {{ etudiantsGrowthLabel }}
                            </span>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.totalEtudiants }}</p>
                        <p class="text-sm text-indigo-100 mt-1">Étudiants approuvés</p>
                    </div>

                    <div class="bg-gradient-to-br from-violet-600 to-violet-500 rounded-2xl p-5 text-white shadow-lg shadow-violet-200 dark:shadow-violet-900/30">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl">👨‍🏫</span>
                            <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full font-medium">
                                {{ conseillersGrowthLabel }}
                            </span>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.totalConseillers }}</p>
                        <p class="text-sm text-violet-100 mt-1">Conseillers approuvés</p>
                    </div>

                    <div class="bg-gradient-to-br from-amber-500 to-amber-400 rounded-2xl p-5 text-white shadow-lg shadow-amber-200 dark:shadow-amber-900/30">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl">⏳</span>
                            <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full font-medium">En attente</span>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.totalPending }}</p>
                        <p class="text-sm text-amber-100 mt-1">Comptes à valider</p>
                    </div>

                    <div class="bg-gradient-to-br from-emerald-600 to-emerald-500 rounded-2xl p-5 text-white shadow-lg shadow-emerald-200 dark:shadow-emerald-900/30">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl">👥</span>
                            <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full font-medium">Total</span>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.totalEtudiants + stats.totalConseillers }}</p>
                        <p class="text-sm text-emerald-100 mt-1">Utilisateurs actifs</p>
                    </div>
                </div>

                <!-- ── Ligne 2 : Pie + Pending Column ─────────────────────── -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Pie — répartition utilisateurs -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4">
                            🥧 Répartition des utilisateurs
                        </h3>
                        <div class="flex items-center gap-6">
                            <canvas ref="pieUsersRef" style="max-width:180px;max-height:180px"></canvas>
                            <div class="space-y-3">
                                <div v-for="(label, i) in stats.pieUtilisateurs.labels" :key="i"
                                     class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0"
                                          :style="{ background: usersColors[i] }"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ label }}</span>
                                    <span class="font-bold text-gray-900 dark:text-white ml-2">
                                        {{ stats.pieUtilisateurs.valeurs[i] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column — comptes en attente par rôle -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4">
                            ⏳ Comptes en attente par rôle
                        </h3>
                        <canvas ref="columnPendingRef" style="max-height:200px"></canvas>
                        <p class="text-xs text-center text-gray-400 mt-3">
                            Total : <strong>{{ stats.totalPending }}</strong> compte(s) à valider
                        </p>
                    </div>
                </div>

                <!-- ── Ligne 3 : Line — Croissance mensuelle (12 mois) ─────── -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="font-semibold text-gray-800 dark:text-white mb-1">
                        📈 Croissance des utilisateurs (12 derniers mois)
                    </h3>
                    <p class="text-xs text-gray-400 mb-4">Évolution mensuelle des étudiants et conseillers approuvés</p>
                    <canvas ref="lineGrowthRef" style="max-height:240px"></canvas>
                </div>

                <!-- ── Ligne 4 : Area + Bar horizontal ───────────────────── -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Area — inscriptions hebdomadaires -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4">
                            🌊 Inscriptions par semaine
                        </h3>
                        <canvas ref="areaInscrRef" style="max-height:220px"></canvas>
                    </div>

                    <!-- Bar horizontal — top filières -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4">
                            🎓 Top filières (nb étudiants)
                        </h3>
                        <canvas ref="barFilieresRef" style="max-height:220px"></canvas>
                    </div>
                </div>

                <!-- ── Ligne 5 : Column-Line — activité chat ──────────────── -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="font-semibold text-gray-800 dark:text-white mb-1">
                        💬 Activité du chat (30 derniers jours)
                    </h3>
                    <p class="text-xs text-gray-400 mb-4">Conversations créées vs messages envoyés</p>
                    <canvas ref="colLineRef" style="max-height:240px"></canvas>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    stats: { type: Object, required: true },
})

// Canvas refs
const pieUsersRef      = ref(null)
const columnPendingRef = ref(null)
const lineGrowthRef    = ref(null)
const areaInscrRef     = ref(null)
const barFilieresRef   = ref(null)
const colLineRef       = ref(null)

// Couleurs
const usersColors = ['#4f46e5', '#7c3aed', '#9ca3af']

// Labels croissance
const etudiantsGrowthLabel = computed(() => {
    const g = props.stats.etudiantsGrowth
    return g >= 0 ? `+${g}%` : `${g}%`
})
const conseillersGrowthLabel = computed(() => {
    const g = props.stats.conseillersGrowth
    return g >= 0 ? `+${g}%` : `${g}%`
})

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

const isDark   = () => document.documentElement.classList.contains('dark')
const textColor = () => isDark() ? '#cbd5e1' : '#374151'
const gridColor = () => isDark() ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)'

async function loadChart() {
    const { Chart, registerables } = await import('chart.js')
    Chart.register(...registerables)
    return Chart
}

// ─────────────────────────────────────────────────────────────────────────────
// Montage
// ─────────────────────────────────────────────────────────────────────────────

onMounted(async () => {
    const Chart = await loadChart()
    const tc = textColor()
    const gc = gridColor()
    const dark = isDark()

    // 1. Pie — répartition utilisateurs
    new Chart(pieUsersRef.value, {
        type: 'pie',
        data: {
            labels:   props.stats.pieUtilisateurs.labels,
            datasets: [{
                data:            props.stats.pieUtilisateurs.valeurs,
                backgroundColor: usersColors,
                borderWidth:     2,
                borderColor:     dark ? '#1e293b' : '#fff',
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.label} : ${ctx.parsed}` } },
            },
        },
    })

    // 2. Column — pending par rôle
    new Chart(columnPendingRef.value, {
        type: 'bar',
        data: {
            labels:   props.stats.pendingParRole.labels,
            datasets: [{
                label:           'Comptes en attente',
                data:            props.stats.pendingParRole.valeurs,
                backgroundColor: ['#f59e0b', '#f97316'],
                borderRadius:    8,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: tc, font: { size: 12 } }, grid: { display: false } },
                y: { ticks: { color: tc }, grid: { color: gc } },
            },
        },
    })

    // 3. Line — croissance mensuelle
    new Chart(lineGrowthRef.value, {
        type: 'line',
        data: {
            labels: props.stats.croissanceMensuelle.labels,
            datasets: [
                {
                    label:           'Étudiants',
                    data:            props.stats.croissanceMensuelle.etudiants,
                    borderColor:     '#4f46e5',
                    backgroundColor: 'transparent',
                    tension:         0.4,
                    pointRadius:     4,
                    pointBackgroundColor: '#4f46e5',
                },
                {
                    label:           'Conseillers',
                    data:            props.stats.croissanceMensuelle.conseillers,
                    borderColor:     '#f59e0b',
                    backgroundColor: 'transparent',
                    tension:         0.4,
                    pointRadius:     4,
                    pointBackgroundColor: '#f59e0b',
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: tc, font: { size: 11 } } } },
            scales: {
                x: { ticks: { color: tc, font: { size: 10 } }, grid: { color: gc } },
                y: { ticks: { color: tc }, grid: { color: gc } },
            },
        },
    })

    // 4. Area — inscriptions hebdomadaires
    new Chart(areaInscrRef.value, {
        type: 'line',
        data: {
            labels: props.stats.inscriptionsHebdo.labels,
            datasets: [
                {
                    label:           'Étudiants',
                    data:            props.stats.inscriptionsHebdo.etudiants,
                    borderColor:     '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.2)',
                    fill:            true,
                    tension:         0.4,
                    pointRadius:     3,
                },
                {
                    label:           'Conseillers',
                    data:            props.stats.inscriptionsHebdo.conseillers,
                    borderColor:     '#7c3aed',
                    backgroundColor: 'rgba(124,58,237,0.15)',
                    fill:            true,
                    tension:         0.4,
                    pointRadius:     3,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: tc, font: { size: 11 } } } },
            scales: {
                x: { ticks: { color: tc }, grid: { color: gc } },
                y: { ticks: { color: tc }, grid: { color: gc } },
            },
        },
    })

    // 5. Bar horizontal — top filières
    new Chart(barFilieresRef.value, {
        type: 'bar',
        data: {
            labels:   props.stats.topFilieres.labels,
            datasets: [{
                label:           'Étudiants',
                data:            props.stats.topFilieres.valeurs,
                backgroundColor: [
                    '#4f46e5','#6366f1','#818cf8','#a5b4fc',
                    '#c7d2fe','#7c3aed','#8b5cf6','#a78bfa',
                ],
                borderRadius:    4,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: tc }, grid: { color: gc } },
                y: { ticks: { color: tc, font: { size: 10 } }, grid: { display: false } },
            },
        },
    })

    // 6. Column-Line — activité chat
    new Chart(colLineRef.value, {
        type: 'bar',
        data: {
            labels: props.stats.activiteChat.labels,
            datasets: [
                {
                    type:            'bar',
                    label:           'Conversations créées',
                    data:            props.stats.activiteChat.conversations,
                    backgroundColor: '#818cf8',
                    borderRadius:    4,
                    yAxisID:         'y',
                },
                {
                    type:            'line',
                    label:           'Messages échangés',
                    data:            props.stats.activiteChat.messages,
                    borderColor:     '#f59e0b',
                    backgroundColor: 'transparent',
                    tension:         0.4,
                    pointRadius:     4,
                    pointBackgroundColor: '#f59e0b',
                    borderDash:      [4, 3],
                    yAxisID:         'y1',
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: tc, font: { size: 11 } } } },
            scales: {
                x:  { ticks: { color: tc, font: { size: 10 } }, grid: { color: gc } },
                y:  { ticks: { color: tc }, grid: { color: gc }, position: 'left' },
                y1: {
                    ticks: { color: '#f59e0b' },
                    grid: { display: false },
                    position: 'right',
                },
            },
        },
    })
})
</script>