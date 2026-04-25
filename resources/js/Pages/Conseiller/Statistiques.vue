<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-6">

                <!-- Header -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white"> Mes Statistiques</h1>
                    
                </div>

<!-- ── KPI Cards – Icônes 3D colorées ────────────────────────────────────── -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
  
  <!-- Carte 1 : Total étudiants -->
  <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
    <div class="flex items-center justify-between mb-1">
      <!-- Icône utilisateurs 3D colorée -->
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-600 shadow-lg flex items-center justify-center transform rotate-3 hover:rotate-0 transition-transform">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
      </div>
    </div>
    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">Total étudiants</div>
    <div class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.totalEtudiants }}</div>
    <!-- Ligne de variation (optionnelle) -->
    <!-- <div class="text-sm text-green-600 dark:text-green-400 mt-2 flex items-center gap-1">▲ +12%</div> -->
  </div>

  <!-- Carte 2 : Conversations prises -->
  <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
    <div class="flex items-center justify-between mb-1">
      <!-- Icône chat 3D colorée -->
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 shadow-lg flex items-center justify-center transform -rotate-3 hover:rotate-0 transition-transform">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
      </div>
    </div>
    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">Conversations prises</div>
    <div class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.mesConversations }}</div>
  </div>

  <!-- Carte 3 : Questionnaires remplis -->
  <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
    <div class="flex items-center justify-between mb-1">
      <!-- Icône document 3D colorée -->
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-pink-500 shadow-lg flex items-center justify-center transform rotate-3 hover:rotate-0 transition-transform">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
    </div>
    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">Questionnaires remplis</div>
    <div class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
      {{ stats.questionnairesRemplis }}<span class="text-lg font-normal text-gray-400 dark:text-gray-500">/{{ stats.totalQuestionnaires }}</span>
    </div>
  </div>

  <!-- Carte 4 : Taux de réponse -->
  <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
    <div class="flex items-center justify-between mb-1">
      <!-- Icône graphique 3D colorée -->
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-400 to-pink-600 shadow-lg flex items-center justify-center transform -rotate-3 hover:rotate-0 transition-transform">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white drop-shadow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
      </div>
    </div>
    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">Taux de réponse</div>
    <div class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.tauxReponse }}%</div>
  </div>
</div>

                <!-- ── Ligne 2 : Pie + Column ──────────────────────────────── -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Pie — Questionnaires remplis vs non remplis -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            Participation au questionnaire
                        </h3>
                        <div class="flex items-center gap-6">
                            <canvas ref="pieQuestRef" style="max-width:180px;max-height:180px"></canvas>
                            <div class="space-y-2">
                                <div v-for="(label, i) in stats.pieQuestionnaires.labels" :key="i"
                                     class="flex items-center gap-2 text-sm">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0"
                                          :style="{ background: pieColors[i] }"></span>
                                    <span class="text-gray-600 dark:text-gray-300">{{ label }}</span>
                                    <span class="font-bold text-gray-900 dark:text-white ml-auto">
                                        {{ stats.pieQuestionnaires.valeurs[i] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column — Scores par filière -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            Score moyen par filière
                        </h3>
                        <canvas ref="columnScoresRef" style="max-height:200px"></canvas>
                    </div>
                </div>

                <!-- ── Ligne 3 : Area — Évolution conversations ────────────── -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        Évolution des conversations (8 dernières semaines)
                    </h3>
                    <canvas ref="areaConvRef" style="max-height:220px"></canvas>
                </div>

                <!-- ── Ligne 4 : Bar + Column-Line ────────────────────────── -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Bar horizontal — filières recommandées -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            Filières les plus recommandées
                        </h3>
                        <canvas ref="barFilieresRef" style="max-height:260px"></canvas>
                    </div>

                    <!-- Column-Line — Activité 7 jours -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                         Activité des 7 derniers jours
                        </h3>
                        <canvas ref="colLineRef" style="max-height:260px"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    stats: { type: Object, required: true },
})

// Canvas refs
const pieQuestRef    = ref(null)
const columnScoresRef = ref(null)
const areaConvRef    = ref(null)
const barFilieresRef = ref(null)
const colLineRef     = ref(null)

// Couleurs cohérentes
const pieColors      = ['#4f46e5', '#e5e7eb', '#818cf8', '#c7d2fe']
const columnColors   = ['#4f46e5', '#818cf8', '#6366f1', '#a5b4fc', '#c7d2fe', '#e0e7ff', '#f5f3ff', '#ede9fe']
const areaColor1     = 'rgba(79,70,229,0.25)'
const areaColor2     = 'rgba(245,158,11,0.18)'
const barColor       = '#6366f1'
const lineColor      = '#f59e0b'

// ─────────────────────────────────────────────────────────────────────────────
// Helpers Chart.js
// ─────────────────────────────────────────────────────────────────────────────

const isDark = () => document.documentElement.classList.contains('dark')
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

    // 1. Pie — questionnaires
    new Chart(pieQuestRef.value, {
        type: 'pie',
        data: {
            labels:   props.stats.pieQuestionnaires.labels,
            datasets: [{
                data:            props.stats.pieQuestionnaires.valeurs,
                backgroundColor: pieColors,
                borderWidth:     2,
                borderColor:     isDark() ? '#1e293b' : '#fff',
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

    // 2. Column — scores par filière
    new Chart(columnScoresRef.value, {
        type: 'bar',
        data: {
            labels:   props.stats.scoresParFiliere.labels,
            datasets: [{
                label:           'Score moyen (%)',
                data:            props.stats.scoresParFiliere.scores,
                backgroundColor: columnColors,
                borderRadius:    6,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: tc, font: { size: 10 } }, grid: { color: gc } },
                y: { ticks: { color: tc }, grid: { color: gc }, max: 100,
                     title: { display: true, text: '%', color: tc } },
            },
        },
    })

    // 3. Area — évolution conversations
    new Chart(areaConvRef.value, {
        type: 'line',
        data: {
            labels: props.stats.evolutionConvs.labels,
            datasets: [
                {
                    label:           'Prises en charge',
                    data:            props.stats.evolutionConvs.prises,
                    borderColor:     '#4f46e5',
                    backgroundColor: areaColor1,
                    fill:            true,
                    tension:         0.4,
                    pointRadius:     4,
                    pointBackgroundColor: '#4f46e5',
                },
                {
                    label:           'En attente',
                    data:            props.stats.evolutionConvs.en_attente,
                    borderColor:     '#f59e0b',
                    backgroundColor: areaColor2,
                    fill:            true,
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
                x: { ticks: { color: tc }, grid: { color: gc } },
                y: { ticks: { color: tc }, grid: { color: gc } },
            },
        },
    })

    // 4. Bar horizontal — filières recommandées
    const filiereLabels = props.stats.filieresTrending.map(f => f.nom)
    const filiereNb     = props.stats.filieresTrending.map(f => f.nb)

    new Chart(barFilieresRef.value, {
        type: 'bar',
        data: {
            labels:   filiereLabels,
            datasets: [{
                label:           'Nb étudiants (top reco)',
                data:            filiereNb,
                backgroundColor: columnColors,
                borderRadius:    5,
            }],
        },
        options: {
            indexAxis: 'y',   // ← Bar horizontal
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: tc }, grid: { color: gc } },
                y: { ticks: { color: tc, font: { size: 10 } }, grid: { display: false } },
            },
        },
    })

    // 5. Column-Line — activité 7 jours
    new Chart(colLineRef.value, {
        type: 'bar',
        data: {
            labels: props.stats.activite7Jours.labels,
            datasets: [
                {
                    type:            'bar',
                    label:           'Messages envoyés',
                    data:            props.stats.activite7Jours.messages,
                    backgroundColor: '#818cf8',
                    borderRadius:    5,
                    yAxisID:         'y',
                },
                {
                    type:            'line',
                    label:           'Questionnaires reçus',
                    data:            props.stats.activite7Jours.questionnaires,
                    borderColor:     '#f59e0b',
                    backgroundColor: 'transparent',
                    tension:         0.4,
                    pointRadius:     5,
                    pointBackgroundColor: '#f59e0b',
                    yAxisID:         'y1',
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: tc, font: { size: 11 } } } },
            scales: {
                x:  { ticks: { color: tc }, grid: { color: gc } },
                y:  { ticks: { color: tc }, grid: { color: gc }, position: 'left' },
                y1: { ticks: { color: '#f59e0b' }, grid: { display: false }, position: 'right' },
            },
        },
    })
})
</script>