<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-300">
                    <div class="p-8">
                        <!-- Header avec message de bienvenue -->
                        <div class="mb-8">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                Bonjour, Conseiller! 
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                Voici les statistiques académiques de votre plateforme.
                            </p>
                        </div>

                        <!-- Cartes KPI (3 cartes comme dans l'original) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- Carte Total Étudiants -->
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:shadow-md transition-all">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">Total Étudiants</p>
                                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatNumber(stats.etudiants || 0) }}</p>
                                    </div>
                                    <div class="bg-blue-100 dark:bg-blue-900/50 rounded-full p-3">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="stats.etudiantsGrowth >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="text-sm font-medium">
                                        {{ stats.etudiantsGrowth >= 0 ? '+' : '' }}{{ stats.etudiantsGrowth || 0 }}%
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Ce mois vs mois dernier</span>
                                </div>
                            </div>

                            <!-- Carte Total Filières -->
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:shadow-md transition-all">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">Total Filières</p>
                                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.filieres || 0 }}</p>
                                    </div>
                                    <div class="bg-green-100 dark:bg-green-900/50 rounded-full p-3">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="stats.filieresGrowth >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="text-sm font-medium">
                                        {{ stats.filieresGrowth >= 0 ? '+' : '' }}{{ stats.filieresGrowth || 0 }}%
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Ce mois vs mois dernier</span>
                                </div>
                            </div>

                            <!-- Carte Moyenne Générale -->
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:shadow-md transition-all">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">Moyenne Générale</p>
                                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.moyenneGenerale || '0.00' }}</p>
                                    </div>
                                    <div class="bg-yellow-100 dark:bg-yellow-900/50 rounded-full p-3">
                                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="stats.moyenneGrowth >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="text-sm font-medium">
                                        {{ stats.moyenneGrowth >= 0 ? '+' : '' }}{{ stats.moyenneGrowth || 0 }}%
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Ce mois vs mois dernier</span>
                                </div>
                            </div>
                        </div>

                        <!-- Section des diagrammes -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Diagramme à barres - Répartition par filière -->
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Répartition par filière</h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Top 5 filières</span>
                                </div>
                                <div class="relative" style="height: 300px;">
                                    <canvas ref="barChart"></canvas>
                                </div>
                            </div>

                            <!-- Diagramme circulaire - Répartition des étudiants -->
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Répartition des étudiants</h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Par filière</span>
                                </div>
                                <div class="relative" style="height: 300px;">
                                    <canvas ref="pieChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Deuxième ligne de diagrammes -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
                            <!-- Diagramme en ligne - Évolution des inscriptions -->
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Évolution des inscriptions</h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">8 dernières semaines</span>
                                </div>
                                <div class="relative" style="height: 300px;">
                                    <canvas ref="lineChart"></canvas>
                                </div>
                            </div>

                            <!-- Diagramme de performance par filière -->
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Performance par filière</h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Moyennes</span>
                                </div>
                                <div class="relative" style="height: 300px;">
                                    <canvas ref="radarChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques supplémentaires -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.nouveauxEtudiants || 0 }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">nouveaux étudiants ce mois</p>
                                    </div>
                                    <div class="bg-green-100 dark:bg-green-900/50 rounded-full p-3">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.tauxReussite || 0 }}%</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">taux de réussite global</p>
                                    </div>
                                    <div class="bg-blue-100 dark:bg-blue-900/50 rounded-full p-3">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Chart from 'chart.js/auto'

const props = defineProps({
    stats: Object
})

const barChart = ref(null)
const pieChart = ref(null)
const lineChart = ref(null)
const radarChart = ref(null)

let barChartInstance = null
let pieChartInstance = null
let lineChartInstance = null
let radarChartInstance = null

const formatNumber = (num) => {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ")
}

const createCharts = () => {
    // Données pour les diagrammes
    const filiereData = props.stats.filieresRepartition || {
        labels: ['Informatique', 'Gestion', 'Génie Civil', 'Électronique', 'Marketing'],
        valeurs: [35, 28, 18, 12, 7]
    }
    
    const performanceData = props.stats.performanceParFiliere || {
        labels: ['Informatique', 'Gestion', 'Génie Civil', 'Électronique', 'Marketing'],
        valeurs: [15.5, 14.2, 13.8, 14.5, 13.2]
    }
    
    const evolutionData = props.stats.evolution || {
        labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6', 'Sem 7', 'Sem 8'],
        valeurs: [12, 19, 15, 22, 28, 35, 42, props.stats.etudiants || 0]
    }

    // Diagramme à barres - Répartition par filière
    if (barChartInstance) {
        barChartInstance.destroy()
    }
    barChartInstance = new Chart(barChart.value, {
        type: 'bar',
        data: {
            labels: filiereData.labels,
            datasets: [{
                label: 'Nombre d\'étudiants',
                data: filiereData.valeurs,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.raw} étudiants`
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: '#E5E7EB'
                    },
                    title: {
                        display: true,
                        text: 'Nombre d\'étudiants'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    })

    // Diagramme circulaire - Répartition des étudiants
    if (pieChartInstance) {
        pieChartInstance.destroy()
    }
    pieChartInstance = new Chart(pieChart.value, {
        type: 'pie',
        data: {
            labels: filiereData.labels,
            datasets: [{
                data: filiereData.valeurs,
                backgroundColor: [
                    '#3B82F6',
                    '#10B981',
                    '#F59E0B',
                    '#8B5CF6',
                    '#EF4444'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || ''
                            const value = context.raw || 0
                            const total = context.dataset.data.reduce((a, b) => a + b, 0)
                            const percentage = ((value / total) * 100).toFixed(1)
                            return `${label}: ${value} (${percentage}%)`
                        }
                    }
                }
            }
        }
    })

    // Diagramme en ligne - Évolution des inscriptions
    if (lineChartInstance) {
        lineChartInstance.destroy()
    }
    lineChartInstance = new Chart(lineChart.value, {
        type: 'line',
        data: {
            labels: evolutionData.labels,
            datasets: [{
                label: 'Inscriptions',
                data: evolutionData.valeurs,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: '#E5E7EB'
                    },
                    title: {
                        display: true,
                        text: 'Nombre d\'inscriptions'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    })

    // Diagramme radar - Performance par filière
    if (radarChartInstance) {
        radarChartInstance.destroy()
    }
    radarChartInstance = new Chart(radarChart.value, {
        type: 'radar',
        data: {
            labels: performanceData.labels,
            datasets: [{
                label: 'Moyenne (/20)',
                data: performanceData.valeurs,
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: '#3B82F6',
                borderWidth: 2,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 20,
                    ticks: {
                        stepSize: 5
                    },
                    grid: {
                        color: '#E5E7EB'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}/20`
                        }
                    }
                }
            }
        }
    })
}

onMounted(() => {
    createCharts()
})

watch(() => props.stats, () => {
    createCharts()
}, { deep: true })
</script>

<style scoped>
canvas {
    max-width: 100%;
}
</style>