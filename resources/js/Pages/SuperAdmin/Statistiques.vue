<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8">
                        <!-- Header avec message de bienvenue -->
                        <div class="mb-8">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                                Bonjour, Administrateur! 
                            </h1>
                            <p class="text-gray-600">
                                Voici ce qui se passe dans votre plateforme ce mois-ci.
                            </p>
                        </div>

                        <!-- Cartes KPI (3 cartes comme dans l'image) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- Carte Total Utilisateurs -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500 font-medium mb-1">Total Utilisateurs</p>
                                        <p class="text-3xl font-bold text-gray-900">{{ formatNumber(stats.users || 0) }}</p>
                                    </div>
                                    <div class="bg-blue-100 rounded-full p-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="stats.usersGrowth >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                                        {{ stats.usersGrowth >= 0 ? '+' : '' }}{{ stats.usersGrowth || 0 }}%
                                    </span>
                                    <span class="text-xs text-gray-500">Ce mois vs mois dernier</span>
                                </div>
                            </div>

                            <!-- Carte Étudiants -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500 font-medium mb-1">Étudiants</p>
                                        <p class="text-3xl font-bold text-gray-900">{{ formatNumber(stats.etudiants || 0) }}</p>
                                    </div>
                                    <div class="bg-green-100 rounded-full p-3">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="stats.etudiantsGrowth >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                                        {{ stats.etudiantsGrowth >= 0 ? '+' : '' }}{{ stats.etudiantsGrowth || 0 }}%
                                    </span>
                                    <span class="text-xs text-gray-500">Ce mois vs mois dernier</span>
                                </div>
                            </div>

                            <!-- Carte Conseillers -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500 font-medium mb-1">Conseillers</p>
                                        <p class="text-3xl font-bold text-gray-900">{{ formatNumber(stats.conseillers || 0) }}</p>
                                    </div>
                                    <div class="bg-purple-100 rounded-full p-3">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="stats.conseillersGrowth >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                                        {{ stats.conseillersGrowth >= 0 ? '+' : '' }}{{ stats.conseillersGrowth || 0 }}%
                                    </span>
                                    <span class="text-xs text-gray-500">Ce mois vs mois dernier</span>
                                </div>
                            </div>
                        </div>

                        <!-- Deuxième ligne de cartes -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Carte Filières -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500 font-medium mb-1">Filières actives</p>
                                        <p class="text-3xl font-bold text-gray-900">{{ stats.filieres || 0 }}</p>
                                    </div>
                                    <div class="bg-yellow-100 rounded-full p-3">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="stats.filieresGrowth >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                                        {{ stats.filieresGrowth >= 0 ? '+' : '' }}{{ stats.filieresGrowth || 0 }}%
                                    </span>
                                    <span class="text-xs text-gray-500">Ce mois vs mois dernier</span>
                                </div>
                            </div>

                            <!-- Carte Inscriptions récentes -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500 font-medium mb-1">Inscriptions ce mois</p>
                                        <p class="text-3xl font-bold text-gray-900">{{ stats.inscriptionsMois || 0 }}</p>
                                    </div>
                                    <div class="bg-indigo-100 rounded-full p-3">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500">Nouveaux utilisateurs</span>
                                </div>
                            </div>
                        </div>

                        <!-- Section des diagrammes -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Diagramme d'évolution -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900">Évolution des inscriptions</h3>
                                    <span class="text-sm text-gray-500">Ce mois vs mois dernier</span>
                                </div>
                                <div class="relative" style="height: 300px;">
                                    <canvas ref="evolutionChart"></canvas>
                                </div>
                            </div>

                            <!-- Diagramme par catégorie -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900">Répartition par filière</h3>
                                    <span class="text-sm text-gray-500">Top 5 filières</span>
                                </div>
                                <div class="relative" style="height: 300px;">
                                    <canvas ref="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques supplémentaires (comme dans la capture) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-2xl font-bold text-gray-900">{{ stats.enAttente || 0 }}</p>
                                        <p class="text-sm text-gray-600 mt-1">inscriptions sont en attente de confirmation</p>
                                    </div>
                                    <div class="bg-orange-100 rounded-full p-3">
                                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-2xl font-bold text-gray-900">{{ stats.enAttenteReponse || 0 }}</p>
                                        <p class="text-sm text-gray-600 mt-1">étudiants sont en attente de réponse</p>
                                    </div>
                                    <div class="bg-blue-100 rounded-full p-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
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

const evolutionChart = ref(null)
const categoryChart = ref(null)
let evolutionChartInstance = null
let categoryChartInstance = null

const formatNumber = (num) => {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ")
}

const createCharts = () => {
    // Données simulées pour l'évolution (vous pouvez remplacer par vos vraies données)
    const evolutionData = props.stats.evolution || {
        labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6', 'Sem 7', 'Sem 8'],
        etudiants: [12, 19, 15, 22, 28, 35, 42, props.stats.etudiants || 0],
        conseillers: [5, 8, 7, 10, 12, 14, 16, props.stats.conseillers || 0]
    }

    // Diagramme d'évolution (ligne)
    if (evolutionChartInstance) {
        evolutionChartInstance.destroy()
    }
    evolutionChartInstance = new Chart(evolutionChart.value, {
        type: 'line',
        data: {
            labels: evolutionData.labels,
            datasets: [
                {
                    label: 'Étudiants',
                    data: evolutionData.etudiants,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Conseillers',
                    data: evolutionData.conseillers,
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
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

    // Données pour le diagramme par filière
    const categoryData = props.stats.filieresRepartition || {
        labels: ['Informatique', 'Gestion', 'Génie Civil', 'Électronique', 'Marketing'],
        valeurs: [35, 28, 18, 12, 7]
    }

    // Diagramme à barres horizontales pour les catégories
    if (categoryChartInstance) {
        categoryChartInstance.destroy()
    }
    categoryChartInstance = new Chart(categoryChart.value, {
        type: 'bar',
        data: {
            labels: categoryData.labels,
            datasets: [{
                label: 'Nombre d\'étudiants',
                data: categoryData.valeurs,
                backgroundColor: [
                    '#3B82F6',
                    '#10B981',
                    '#F59E0B',
                    '#8B5CF6',
                    '#EF4444'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y', // Barres horizontales comme dans la capture
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
                x: {
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
                y: {
                    grid: {
                        display: false
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