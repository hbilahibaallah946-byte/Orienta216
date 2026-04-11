<template>
    <!-- 
        RecommandationsCard.vue
        Composant réutilisable affiché dans le dashboard étudiant
        pour montrer les recommandations de filières.
    -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-lg">🏆 Mes Recommandations</h2>
                    <p class="text-xs text-indigo-200 mt-0.5">Basées sur votre profil et questionnaire</p>
                </div>
                <button @click="recalculer" :disabled="loading"
                        class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-full transition flex items-center gap-1">
                    <span v-if="loading" class="animate-spin">⏳</span>
                    <span v-else>🔄</span>
                    Recalculer
                </button>
            </div>
        </div>

        <!-- Contenu -->
        <div class="p-4">
            <div v-if="loading" class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Aucun profil -->
            <div v-else-if="!profil && recommandations.length === 0"
                 class="text-center py-8">
                <div class="text-5xl mb-3">📝</div>
                <p class="text-gray-500 dark:text-gray-400 font-medium">Questionnaire non rempli</p>
                <p class="text-sm text-gray-400 mt-1">Remplissez le questionnaire pour obtenir vos recommandations personnalisées.</p>
                <a href="/etudiant/questionnaire"
                   class="mt-4 inline-block bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                    Remplir le questionnaire →
                </a>
            </div>

            <!-- Recommandations -->
            <div v-else class="space-y-3">

                <!-- Top 3 filières -->
                <div v-for="reco in recommandations" :key="reco.rang"
                     class="relative overflow-hidden rounded-xl border transition hover:shadow-md cursor-pointer"
                     :class="reco.rang === 1
                        ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20'
                        : reco.rang === 2
                        ? 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/30'
                        : 'border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20'">
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <!-- Médaille -->
                            <div class="text-3xl flex-shrink-0">
                                {{ reco.rang === 1 ? '🥇' : reco.rang === 2 ? '🥈' : '🥉' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="font-bold text-gray-800 dark:text-white text-sm leading-tight">
                                        {{ reco.filiere.nom }}
                                    </h3>
                                    <span class="font-bold text-lg flex-shrink-0"
                                          :class="reco.score >= 80 ? 'text-green-600' : reco.score >= 60 ? 'text-yellow-600' : 'text-red-500'">
                                        {{ reco.score }}%
                                    </span>
                                </div>

                                <p v-if="reco.filiere.universite"
                                   class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    🏛️ {{ reco.filiere.universite }}
                                </p>

                                <!-- Barre de compatibilité -->
                                <div class="mt-2">
                                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                        <span>Compatibilité</span>
                                        <span>{{ compatibiliteLabel(reco.score) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all duration-700"
                                             :class="reco.score >= 80 ? 'bg-green-500' : reco.score >= 60 ? 'bg-yellow-500' : 'bg-red-400'"
                                             :style="{ width: reco.score + '%' }"></div>
                                    </div>
                                </div>

                                <!-- Badge contextuel -->
                                <div class="mt-2">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                          :class="reco.score >= 80
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                            : reco.score >= 60
                                            ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'
                                            : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'">
                                        {{ compatibiliteMessage(reco.score) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profil résumé -->
                <div v-if="profil" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                        🧾 Votre profil détecté
                    </p>
                    <div class="space-y-2">
                        <div v-if="profil.interets?.length" class="flex flex-wrap gap-1">
                            <span class="text-xs text-gray-500 dark:text-gray-400 w-full">❤️ Intérêts :</span>
                            <span v-for="tag in profil.interets.slice(0, 5)" :key="tag"
                                  class="text-xs bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">
                                {{ tag }}
                            </span>
                        </div>
                        <div v-if="profil.competences?.length" class="flex flex-wrap gap-1">
                            <span class="text-xs text-gray-500 dark:text-gray-400 w-full">🛠️ Compétences :</span>
                            <span v-for="tag in profil.competences.slice(0, 5)" :key="tag"
                                  class="text-xs bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-full">
                                {{ tag }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- CTA chat -->
                <div class="mt-3 p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-700">
                    <p class="text-xs text-indigo-700 dark:text-indigo-300">
                        💬 Des questions sur ces recommandations ?
                        Chattez avec un conseiller pour en savoir plus.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const loading          = ref(false)
const profil           = ref(null)
const recommandations  = ref([])

const fetchProfil = async () => {
    loading.value = true
    try {
        const res  = await fetch('/api/profil/moi')
        const data = await res.json()
        profil.value          = data.profil
        recommandations.value = data.recommandations || []
    } catch (e) {
        console.error('fetchProfil:', e)
    } finally {
        loading.value = false
    }
}

const recalculer = async () => {
    loading.value = true
    try {
        await fetch('/api/profil/recalculer', { method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
        })
        await fetchProfil()
    } catch (e) {
        console.error('recalculer:', e)
        loading.value = false
    }
}

const compatibiliteLabel = (score) => {
    if (score >= 80) return '✅ Excellente'
    if (score >= 60) return '👍 Bonne'
    if (score >= 40) return '⚠️ Moyenne'
    return '❓ Faible'
}

const compatibiliteMessage = (score) => {
    if (score >= 80) return 'Correspond très bien à votre profil'
    if (score >= 60) return 'Bonne option, à explorer'
    return 'Option possible, discutez avec un conseiller'
}

onMounted(fetchProfil)
</script>