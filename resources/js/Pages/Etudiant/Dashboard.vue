<template>
    <div class="min-h-screen flex bg-gray-100 dark:bg-gray-900">

        <!-- ── Sidebar ──────────────────────────────────────────────── -->
        <div :class="[
                'fixed inset-y-0 left-0 bg-indigo-700 text-white w-64 p-6 space-y-2 transform transition-transform duration-300 z-40',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'md:translate-x-0 md:relative md:z-0'
             ]">
            <h2 class="text-2xl font-bold mb-6">🎓 Mon Parcours</h2>
            <nav class="space-y-1">
                <button v-for="tab in tabs" :key="tab.id"
                        @click="activeTab = tab.id"
                        class="w-full text-left py-2.5 px-4 rounded-xl transition relative flex items-center gap-2"
                        :class="activeTab === tab.id ? 'bg-white/20 font-semibold' : 'hover:bg-white/10'">
                    <span>{{ tab.icon }}</span>{{ tab.label }}
                    <span v-if="tab.id === 'favoris' && favoriteFilieres.length"
                          class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        {{ favoriteFilieres.length }}
                    </span>
                </button>

                <!-- Boutons de navigation externe -->
                <button @click="goToQuestionnaires"
                        class="w-full text-left py-2.5 px-4 rounded-xl bg-white/20 hover:bg-white/30 transition mt-2 flex items-center gap-2">
                    📝 Mes Questionnaires
                </button>
                <button @click="goToCalcul"
                        class="w-full text-left py-2.5 px-4 rounded-xl bg-white/20 hover:bg-white/30 transition mt-1 flex items-center gap-2">
                    🧮 Calculer une moyenne
                </button>
                <button @click="goToPrivateUniversities"
                        class="w-full text-left py-2.5 px-4 rounded-xl bg-white/20 hover:bg-white/30 transition mt-1 flex items-center gap-2">
                    🏛️ Universités privées
                </button>
            </nav>

            <button @click="logout"
                    class="absolute bottom-6 left-6 right-6 py-2 px-4 bg-red-600 rounded-xl hover:bg-red-700 transition text-sm font-medium">
                Se déconnecter
            </button>
        </div>

        <!-- Mobile toggle -->
        <button @click="sidebarOpen = !sidebarOpen"
                class="fixed top-4 left-4 md:hidden z-50 bg-indigo-700 p-3 rounded-xl text-white shadow-lg"
                :class="{ 'left-64': sidebarOpen }">
            <span v-if="!sidebarOpen">☰</span><span v-else>✕</span>
        </button>

        <!-- ── Contenu principal ─────────────────────────────────────── -->
        <main class="flex-1 p-4 md:p-8 overflow-auto">

            <!-- ── FILIÈRES ────────────────────────────────────────── -->
            <div v-if="activeTab === 'filieres'" class="space-y-4">
                <div>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Critères (mots-clés séparés par virgule)
  </label>
  <input
    v-model="criteresInput"
    type="text"
    placeholder="logique, maths, programmation..."
    class="w-full border rounded-lg p-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
  />
  <p class="text-xs text-gray-400 mt-1">
    Ces mots-clés servent à calculer la compatibilité avec le profil des étudiants.
  </p>
</div>
                <input v-model="search" type="text"
                       placeholder="Rechercher une filière…"
                       class="w-full max-w-md p-3 border rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"/>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                    <h2 class="text-xl font-bold mb-4 text-indigo-700 dark:text-indigo-400">Filières disponibles</h2>
                    <div v-if="!props.filieres?.length" class="text-gray-400 text-center py-8">Aucune filière</div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full border-collapse text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">الاختصاص</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">الرمز</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">المؤسسة</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">نوع الباك</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">المجموعة</th>
                                    <th class="p-3 text-center">❤️</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="f in filteredFilieres" :key="f.id"
                                    class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <td class="p-3 dark:text-white">{{ f.specialite || f.nom }}</td>
                                    <td class="p-3 dark:text-white">{{ f.code || '-' }}</td>
                                    <td class="p-3 dark:text-white">{{ f.universite || '-' }}</td>
                                    <td class="p-3 dark:text-white">{{ f.type_bac || '-' }}</td>
                                    <td class="p-3 dark:text-white">{{ f.annee || '-' }}</td>
                                    <td class="p-3 text-center">
                                        <button @click="toggleFav(f.id)"
                                                class="text-xl transition hover:scale-110"
                                                :class="isFav(f.id) ? 'text-red-500' : 'text-gray-300 hover:text-red-400'">❤️</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── FAVORIS ─────────────────────────────────────────── -->
            <div v-if="activeTab === 'favoris'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                <h2 class="text-xl font-bold mb-4 text-indigo-700 dark:text-indigo-400">Mes filières favorites ❤️</h2>
                <div v-if="!favFilieresData.length" class="text-gray-400 text-center py-8">Aucun favori</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="p-3 text-left text-gray-600 dark:text-gray-300">الاختصاص</th>
                                <th class="p-3 text-left text-gray-600 dark:text-gray-300">الجامعة</th>
                                <th class="p-3 text-left text-gray-600 dark:text-gray-300">نوع الباك</th>
                                <th class="p-3 text-left text-gray-600 dark:text-gray-300">المجموعة</th>
                                <th class="p-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="f in favFilieresData" :key="f.id"
                                class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="p-3 dark:text-white">{{ f.specialite || f.nom }}</td>
                                <td class="p-3 dark:text-white">{{ f.universite || '-' }}</td>
                                <td class="p-3 dark:text-white">{{ f.type_bac || '-' }}</td>
                                <td class="p-3 dark:text-white">{{ f.annee || '-' }}</td>
                                <td class="p-3 text-center">
                                    <button @click="toggleFav(f.id)" class="text-xl text-red-500 hover:scale-110 transition">💔</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── MES MOYENNES ───────────────────────────────────── -->
            <div v-if="activeTab === 'mesMoyennes'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                <h2 class="text-xl font-bold mb-4 text-indigo-700 dark:text-indigo-400">Mes Moyennes</h2>
                <div v-if="moyennesStore.loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>
                <div v-else-if="!moyennesStore.moyennes.length" class="text-gray-400 text-center py-8">
                    Aucune moyenne enregistrée
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="m in moyennesStore.moyennes" :key="m.id"
                         @click="openModal(m)"
                         class="rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden cursor-pointer hover:shadow-lg transition">
                        <div class="bg-indigo-600 p-4">
                            <h3 class="font-bold text-white">{{ m.specialite }}</h3>
                            <p class="text-indigo-100 text-xs mt-0.5">{{ fmtDate(m.created_at) }}</p>
                        </div>
                        <div class="p-4">
                            <div class="flex justify-between items-baseline mb-1">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Moyenne</span>
                                <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ m.moyenne }}/20</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: (m.moyenne / 20 * 100) + '%' }"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t dark:border-gray-600 text-xs">
                                <div><p class="text-gray-400">Score ×2</p><p class="font-semibold text-green-600">{{ m.score }}</p></div>
                                <div><p class="text-gray-400">Score +7%</p><p class="font-semibold text-yellow-600">{{ m.score_plus_7 }}</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modale détails moyenne -->
            <div v-if="showModal"
                 class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
                 @click.self="showModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-xl w-full max-h-[85vh] overflow-y-auto">
                    <div class="sticky top-0 bg-indigo-600 p-5 rounded-t-2xl flex justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-white">{{ selectedM?.specialite }}</h2>
                            <p class="text-indigo-100 text-xs mt-0.5">{{ selectedM ? fmtDate(selectedM.created_at) : '' }}</p>
                        </div>
                        <button @click="showModal = false" class="text-white text-2xl">×</button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 p-3 rounded-xl text-center">
                                <p class="text-xs text-gray-500">Moyenne</p>
                                <p class="text-2xl font-bold text-indigo-600">{{ selectedM?.moyenne }}/20</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-xl text-center">
                                <p class="text-xs text-gray-500">Score ×2</p>
                                <p class="text-2xl font-bold text-green-600">{{ selectedM?.score }}</p>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-xl text-center">
                                <p class="text-xs text-gray-500">Score +7%</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ selectedM?.score_plus_7 }}</p>
                            </div>
                        </div>
                        <div v-for="(mat, i) in selectedM?.matieres" :key="i"
                             class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium text-sm text-gray-800 dark:text-white">{{ mat.nom }}</span>
                                    <span class="text-xs text-indigo-500 ml-2">Coef {{ mat.coefficient }}</span>
                                </div>
                                <span class="font-bold text-base"
                                      :class="parseFloat(mat.note) >= 12 ? 'text-green-600' : parseFloat(mat.note) >= 10 ? 'text-orange-500' : 'text-red-600'">
                                    {{ mat.note }}/20
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 mt-2">
                                <div class="h-1.5 rounded-full"
                                     :class="parseFloat(mat.note) >= 12 ? 'bg-green-500' : parseFloat(mat.note) >= 10 ? 'bg-orange-500' : 'bg-red-500'"
                                     :style="{ width: (parseFloat(mat.note) / 20 * 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                    <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-700 p-4 rounded-b-2xl flex justify-end">
                        <button @click="showModal = false"
                                class="px-5 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition text-sm">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── PARAMÈTRES ────────────────────────────────────── -->
            <div v-if="activeTab === 'parametres'" class="max-w-2xl space-y-5">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">👤 Mes informations</h2>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-2xl">👤</div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ user?.name }}</p>
                            <p class="text-sm text-gray-500">{{ user?.email }}</p>
                            <p class="text-xs text-gray-400 mt-1">Rôle : Étudiant</p>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
    <ChatIcon />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useMoyennesStore } from '@/stores/moyennes'
import ChatIcon from '@/Components/ChatIcon.vue'


const goToPrivateUniversities = () => router.visit(route('etudiant.private-universities'))

// ── Props ──────────────────────────────────────────────────────────────────────
const criteresInput = ref('')
const criteres = criteresInput.value.split(',').map(s => s.trim().toLowerCase()).filter(Boolean)


const props = defineProps({
    filieres: { type: Array,  default: () => [] },
    user:     { type: Object, default: () => ({}) },
})

// ── Navigation sidebar ─────────────────────────────────────────────────────────
const activeTab   = ref('filieres')
const sidebarOpen = ref(false)

const tabs = [
    { id: 'filieres',    icon: '🎓', label: 'Filières' },
    { id: 'favoris',     icon: '❤️', label: 'Filières Fav' },
    { id: 'mesMoyennes', icon: '📊', label: 'Mes Moyennes' },
    { id: 'parametres',  icon: '⚙️',  label: 'Paramètres' },
]

// ── Store moyennes ─────────────────────────────────────────────────────────────
const moyennesStore = useMoyennesStore()
onMounted(() => {
    moyennesStore.fetchMoyennes()
    const saved = localStorage.getItem('favoriteFilieres')
    if (saved) favoriteFilieres.value = JSON.parse(saved)
})

// ── Filières & Favoris ─────────────────────────────────────────────────────────
const search           = ref('')
const favoriteFilieres = ref([])

const filteredFilieres = computed(() => {
    if (!search.value) return props.filieres
    const s = search.value.toLowerCase()
    return props.filieres.filter(f =>
        (f.specialite || f.nom || '').toLowerCase().includes(s) ||
        (f.code       || '').toLowerCase().includes(s)          ||
        (f.universite || '').toLowerCase().includes(s)
    )
})

const favFilieresData = computed(() =>
    props.filieres.filter(f => favoriteFilieres.value.includes(f.id))
)

const isFav = (id) => favoriteFilieres.value.includes(id)

const toggleFav = (id) => {
    const i = favoriteFilieres.value.indexOf(id)
    i === -1
        ? favoriteFilieres.value.push(id)
        : favoriteFilieres.value.splice(i, 1)
    localStorage.setItem('favoriteFilieres', JSON.stringify(favoriteFilieres.value))
}

// ── Modale moyenne ─────────────────────────────────────────────────────────────
const showModal = ref(false)
const selectedM = ref(null)
const openModal = (m) => { selectedM.value = m; showModal.value = true }

// ── Helpers ────────────────────────────────────────────────────────────────────
const fmtDate = (d) =>
    new Date(d).toLocaleDateString('fr-FR', {
        day: '2-digit', month: '2-digit', year: 'numeric'
    })

// ── Navigation externe ─────────────────────────────────────────────────────────
const goToQuestionnaires = () => router.visit(route('etudiant.questionnaires.index'))
const goToCalcul         = () => router.visit(route('etudiant.calcul-moyennes'))
const logout             = () => router.post('/logout')

// ⚠️ IL NE DOIT PAS Y AVOIR DE CODE ICI APRÈS LES FONCTIONS ⚠️
// Supprimez tout ce qui ressemble à :
// if (!reponses.length) return
// qSendForm.reponses = reponses
</script>

<style scoped>
* { transition: background-color 0.2s ease, border-color 0.2s ease; }
</style>