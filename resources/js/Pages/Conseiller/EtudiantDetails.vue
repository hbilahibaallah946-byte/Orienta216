<!-- resources/js/Pages/Conseiller/EtudiantDetails.vue -->
<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Bouton retour -->
                <div class="mb-4">
                    <Link :href="route('conseiller.etudiants.index')" 
                          class="inline-flex items-center text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour à la liste
                    </Link>
                </div>

                <!-- Informations de l'étudiant -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-300 mb-6">
                    <div class="p-6">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center text-4xl">
                                👨‍🎓
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ etudiant.name }}</h1>
                                <p class="text-gray-600 dark:text-gray-400">{{ etudiant.email }}</p>
                                <p class="text-sm text-indigo-600 dark:text-indigo-400 mt-1">
                                    Filière: {{ etudiant.filiere?.specialite || 'Non assigné' }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                            <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Membre depuis</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ new Date(etudiant.created_at).toLocaleDateString('fr-FR') }}
                                </p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Nombre de moyennes</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ moyennes.length }}
                                </p>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/30 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Filières favorites</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ favoris.length }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau de bord recommandations (scores objectifs) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-300 mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            📊 Recommandations intelligentes
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Classement mixte (questionnaire, moyenne bac, seuils « dernier orienté » importés). Même vue que l’onglet Recommandations du chat.
                        </p>
                        <RecoDashboardPanel :data="recoData" :loading="recoLoading" />
                    </div>
                </div>

                <!-- Moyennes de l'étudiant -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-300 mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            📊 Mes Moyennes
                        </h2>
                        
                        <div v-if="moyennes.length === 0" class="text-center py-8 text-gray-500">
                            Aucune moyenne enregistrée
                        </div>
                        
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="moyenne in moyennes"
                                :key="moyenne.id"
                                class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-700 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-200 dark:border-gray-600 overflow-hidden"
                            >
                                <div class="bg-indigo-600 dark:bg-indigo-700 p-4">
                                    <h3 class="text-xl font-bold text-white">{{ moyenne.specialite }}</h3>
                                    <p class="text-indigo-100 text-sm">
                                        {{ new Date(moyenne.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                    </p>
                                </div>
                                
                                <div class="p-4">
                                    <div class="mb-3">
                                        <div class="flex justify-between items-baseline">
                                            <span class="text-gray-600 dark:text-gray-400 text-sm">Moyenne générale</span>
                                            <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ moyenne.moyenne }}/20</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 mt-1">
                                            <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: (moyenne.moyenne / 20 * 100) + '%' }"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-3 mt-4 pt-3 border-t border-gray-200 dark:border-gray-600">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Score (×2)</p>
                                            <p class="text-lg font-semibold text-green-600 dark:text-green-400">{{ moyenne.score }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Score +7%</p>
                                            <p class="text-lg font-semibold text-yellow-600 dark:text-yellow-400">{{ moyenne.score_plus_7 }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-2 text-center">
                                    <button 
                                        @click="openDetails(moyenne)" 
                                        class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Voir les détails
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filières favorites -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-300">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            ❤️ Filières favorites
                        </h2>
                        
                        <div v-if="favoris.length === 0" class="text-center py-8 text-gray-500">
                            Aucune filière favorite
                        </div>
                        
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full border-collapse">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="p-4 text-left text-gray-600 dark:text-gray-300 font-semibold">الاختصاص الاكبر</th>
                                        <th class="p-4 text-left text-gray-600 dark:text-gray-300 font-semibold">الرمز</th>
                                        <th class="p-4 text-left text-gray-600 dark:text-gray-300 font-semibold">المؤسسة والجامعة</th>
                                        <th class="p-4 text-left text-gray-600 dark:text-gray-300 font-semibold">نوع الباكالوريا</th>
                                        <th class="p-4 text-left text-gray-600 dark:text-gray-300 font-semibold">صيغة احتساب النقاط</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="filiere in favoris" :key="filiere.id" 
                                        class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="p-4 dark:text-white">{{ filiere.specialite || filiere.nom }}</td>
                                        <td class="p-4 dark:text-white">{{ filiere.code || '-' }}</td>
                                        <td class="p-4 dark:text-white">{{ filiere.universite || '-' }}</td>
                                        <td class="p-4 dark:text-white">{{ filiere.type_bac || '-' }}</td>
                                        <td class="p-4 dark:text-white">{{ filiere.formule || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour les détails des moyennes -->
        <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click.self="closeDetails">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-indigo-600 dark:bg-indigo-700 p-6 rounded-t-2xl">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-white">{{ selectedMoyenne?.specialite }}</h2>
                            <p class="text-indigo-100 text-sm mt-1">
                                {{ selectedMoyenne ? new Date(selectedMoyenne.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '' }}
                            </p>
                        </div>
                        <button @click="closeDetails" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-indigo-50 dark:bg-indigo-900/30 p-4 rounded-lg text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Moyenne générale</p>
                            <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ selectedMoyenne?.moyenne }}/20</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Score (Moy × 2)</p>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ selectedMoyenne?.score }}</p>
                        </div>
                        <div class="bg-yellow-50 dark:bg-yellow-900/30 p-4 rounded-lg text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Score +7%</p>
                            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ selectedMoyenne?.score_plus_7 }}</p>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center">
                        <span class="mr-2">📚</span> Détail des notes
                    </h3>
                    
                    <div class="space-y-3">
                        <div
                            v-for="(matiere, index) in selectedMoyenne?.matieres"
                            :key="index"
                            class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                        >
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium text-gray-800 dark:text-white">{{ matiere.nom }}</span>
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400 ml-2">Coef {{ matiere.coefficient }}</span>
                                </div>
                                <div class="text-right">
                                    <span 
                                        class="text-xl font-bold"
                                        :class="{
                                            'text-green-600': parseFloat(matiere.note) >= 12,
                                            'text-orange-500': parseFloat(matiere.note) >= 10 && parseFloat(matiere.note) < 12,
                                            'text-red-600': parseFloat(matiere.note) < 10
                                        }"
                                    >
                                        {{ matiere.note }}/20
                                    </span>
                                    <div class="text-xs text-gray-500">
                                        Poids: {{ (matiere.note * matiere.coefficient).toFixed(2) }} pts
                                    </div>
                                </div>
                            </div>
                            
                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 mt-2">
                                <div 
                                    class="h-1.5 rounded-full transition-all"
                                    :class="{
                                        'bg-green-500': parseFloat(matiere.note) >= 12,
                                        'bg-orange-500': parseFloat(matiere.note) >= 10 && parseFloat(matiere.note) < 12,
                                        'bg-red-500': parseFloat(matiere.note) < 10
                                    }"
                                    :style="{ width: (parseFloat(matiere.note) / 20 * 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Total des coefficients</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ selectedMoyenne?.matieres?.reduce((sum, m) => sum + m.coefficient, 0) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600 dark:text-gray-400">Nombre de matières</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ selectedMoyenne?.matieres?.length }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-700 p-4 rounded-b-2xl flex justify-end">
                    <button @click="closeDetails" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import RecoDashboardPanel from '@/Components/RecoDashboardPanel.vue'

const props = defineProps({
    etudiant: Object,
    moyennes: Array,
    favoris: Array
})

const recoData = ref(null)
const recoLoading = ref(true)

onMounted(async () => {
    try {
        const res = await fetch(`/api/profil/etudiant/${props.etudiant.id}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        if (res.ok) {
            recoData.value = await res.json()
        }
    } catch (e) {
        console.error('reco profil etudiant:', e)
    } finally {
        recoLoading.value = false
    }
})

const selectedMoyenne = ref(null)
const showDetailsModal = ref(false)

function openDetails(moyenne) {
    selectedMoyenne.value = moyenne
    showDetailsModal.value = true
}

function closeDetails() {
    showDetailsModal.value = false
    selectedMoyenne.value = null
}
</script>