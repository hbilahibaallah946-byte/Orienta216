<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useMoyennesStore } from '@/stores/moyennes'

// Initialiser le store
const moyennesStore = useMoyennesStore()

// États
const step = ref('specialites')
const selectedSpecialite = ref(null)
const showDispenseSport = ref(false)
const isSubmitting = ref(false)

// Liste des spécialités
const specialites = [
    { id: 'sc-experimentale', nom: 'Sciences Expérimentales', icon: '🔬' },
    { id: 'math', nom: 'Mathématiques', icon: '📐' },
    { id: 'sport', nom: 'Sport', icon: '⚽' },
    { id: 'lettres', nom: 'Lettres', icon: '📚' },
    { id: 'eco-gestion', nom: 'Économie et Gestion', icon: '📊' },
    { id: 'techniques', nom: 'Sciences Techniques', icon: '⚙️' },
    { id: 'informatique', nom: 'Informatique', icon: '💻' }
]

// Matières par spécialité
const matieresParSpecialite = {
    'sc-experimentale': [
        { nom: 'Mathématiques', coefficient: 3, note: '' },
        { nom: 'Physique-Chimie', coefficient: 3, note: '' },
        { nom: 'Sciences de la Vie et de la Terre', coefficient: 3, note: '' },
        { nom: 'Arabe', coefficient: 2, note: '' },
        { nom: 'Français', coefficient: 2, note: '' },
        { nom: 'Anglais', coefficient: 2, note: '' },
        { nom: 'Sport', coefficient: 1, note: '', hasDispense: false },
        { nom: 'Informatique', coefficient: 1, note: '' },
        { nom: 'Option', coefficient: 1, note: '' }
    ],
    'math': [
        { nom: 'Mathématiques', coefficient: 4, note: '' },
        { nom: 'Physique-Chimie', coefficient: 3, note: '' },
        { nom: 'Sciences', coefficient: 2, note: '' },
        { nom: 'Arabe', coefficient: 2, note: '' },
        { nom: 'Français', coefficient: 2, note: '' },
        { nom: 'Anglais', coefficient: 2, note: '' },
        { nom: 'Sport', coefficient: 1, note: '', hasDispense: false },
        { nom: 'Informatique', coefficient: 1, note: '' },
        { nom: 'Option', coefficient: 1, note: '' }
    ],
    'sport': [
        { nom: 'Éducation Physique et Sportive', coefficient: 4, note: '' },
        { nom: 'Sciences de la Vie', coefficient: 2, note: '' },
        { nom: 'Arabe', coefficient: 2, note: '' },
        { nom: 'Français', coefficient: 2, note: '' },
        { nom: 'Anglais', coefficient: 2, note: '' },
        { nom: 'Mathématiques', coefficient: 2, note: '' },
        { nom: 'Physique', coefficient: 1, note: '' },
        { nom: 'Informatique', coefficient: 1, note: '' }
    ],
    'lettres': [
        { nom: 'Arabe', coefficient: 4, note: '' },
        { nom: 'Français', coefficient: 4, note: '' },
        { nom: 'Anglais', coefficient: 3, note: '' },
        { nom: 'Philosophie', coefficient: 2, note: '' },
        { nom: 'Histoire-Géographie', coefficient: 2, note: '' },
        { nom: 'Mathématiques', coefficient: 1, note: '' },
        { nom: 'Sport', coefficient: 1, note: '', hasDispense: false },
        { nom: 'Informatique', coefficient: 1, note: '' },
        { nom: 'Option', coefficient: 2, note: '' }
    ],
    'eco-gestion': [
        { nom: 'Économie', coefficient: 3, note: '' },
        { nom: 'Gestion', coefficient: 3, note: '' },
        { nom: 'Mathématiques', coefficient: 3, note: '' },
        { nom: 'Arabe', coefficient: 2, note: '' },
        { nom: 'Français', coefficient: 2, note: '' },
        { nom: 'Anglais', coefficient: 2, note: '' },
        { nom: 'Sport', coefficient: 1, note: '', hasDispense: false },
        { nom: 'Informatique', coefficient: 2, note: '' }
    ],
    'techniques': [
        { nom: 'Mathématiques', coefficient: 3, note: '' },
        { nom: 'Physique', coefficient: 3, note: '' },
        { nom: 'Sciences Techniques', coefficient: 3, note: '' },
        { nom: 'Arabe', coefficient: 2, note: '' },
        { nom: 'Français', coefficient: 2, note: '' },
        { nom: 'Anglais', coefficient: 2, note: '' },
        { nom: 'Sport', coefficient: 1, note: '', hasDispense: false },
        { nom: 'Informatique', coefficient: 2, note: '' }
    ],
    'informatique': [
        { nom: 'Mathématiques', coefficient: 3, note: '' },
        { nom: 'Physique', coefficient: 2, note: '' },
        { nom: 'Algorithmique', coefficient: 3, note: '' },
        { nom: 'Programmation', coefficient: 3, note: '' },
        { nom: 'Arabe', coefficient: 2, note: '' },
        { nom: 'Français', coefficient: 2, note: '' },
        { nom: 'Anglais', coefficient: 2, note: '' },
        { nom: 'Sport', coefficient: 1, note: '', hasDispense: false },
        { nom: 'Base de données', coefficient: 2, note: '' }
    ]
}


// Référence réactive pour les matières de la spécialité sélectionnée
const matieres = ref([])

// Calcul de la moyenne pondérée
const moyenne = computed(() => {
    if (!matieres.value.length) return '0.00'
    
    let totalPoints = 0
    let totalCoefficients = 0
    
    matieres.value.forEach(m => {
        if (m.note && m.note !== '') {
            if (m.hasDispense && m.nom === 'Sport') {
                totalPoints += 10 * m.coefficient
                totalCoefficients += m.coefficient
            } else {
                const note = parseFloat(m.note) || 0
                totalPoints += note * m.coefficient
                totalCoefficients += m.coefficient
            }
        }
    })
    
    return totalCoefficients > 0 ? (totalPoints / totalCoefficients).toFixed(2) : '0.00'
})

// Calcul du score (moyenne * 2)
const score = computed(() => {
    return (parseFloat(moyenne.value) * 2).toFixed(2)
})

// Score avec 7% (pour certaines filières)
const scorePlus7 = computed(() => {
    return (parseFloat(moyenne.value) * 2 * 1.07).toFixed(2)
})

// Sélectionner une spécialité
function selectSpecialite(specialite) {
    selectedSpecialite.value = specialite
    matieres.value = JSON.parse(JSON.stringify(matieresParSpecialite[specialite.id]))
    step.value = 'matieres'
}

// Retour à la liste des spécialités
function retourSpecialites() {
    step.value = 'specialites'
    selectedSpecialite.value = null
    matieres.value = []
}

// Gérer la dispense sport
function toggleDispenseSport() {
    const sport = matieres.value.find(m => m.nom === 'Sport')
    if (sport) {
        sport.hasDispense = !sport.hasDispense
        if (sport.hasDispense) {
            sport.note = ''
        }
    }
}

// Soumettre les notes avec le store Pinia
async function allerDashboard() {
    if (isSubmitting.value) return
    
    isSubmitting.value = true
    
    const moyenneData = {
        specialite: selectedSpecialite.value?.nom,
        matieres: matieres.value,
        moyenne: moyenne.value,
        score: score.value,
        score_plus_7: scorePlus7.value
    }
    
    const success = await moyennesStore.addMoyenne(moyenneData)
    
    if (success) {
        // Rediriger vers le dashboard
        router.visit('/etudiant/dashboard')
    } else {
        alert('Erreur lors de l\'enregistrement de la moyenne. Veuillez réessayer.')
    }
    
    isSubmitting.value = false
}

// Vérifier si toutes les notes sont remplies
const allNotesFilled = computed(() => {
    return matieres.value.every(m => {
        if (m.hasDispense && m.nom === 'Sport') return true
        return m.note && m.note !== ''
    })
})
</script>

<template>
    <div class="min-h-screen bg-gray-100 py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="mb-6 flex items-center">
                <button 
                    v-if="step === 'matieres'"
                    @click="retourSpecialites"
                    class="mr-4 text-indigo-600 hover:text-indigo-800"
                >
                    ← Retour
                </button>
                <h1 class="text-3xl font-bold text-indigo-700">
                    {{ step === 'specialites' ? 'Choisissez votre spécialité' : selectedSpecialite?.nom }}
                </h1>
            </div>

            <div v-if="step === 'specialites'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="specialite in specialites"
                    :key="specialite.id"
                    @click="selectSpecialite(specialite)"
                    class="bg-white rounded-xl shadow-lg p-6 cursor-pointer hover:shadow-xl transition transform hover:-translate-y-1"
                >
                    <div class="text-4xl mb-3">{{ specialite.icon }}</div>
                    <h3 class="text-xl font-semibold text-gray-800">{{ specialite.nom }}</h3>
                    <p class="text-sm text-gray-500 mt-2">Cliquez pour voir les matières</p>
                </div>
            </div>

            <div v-else class="bg-white rounded-xl shadow-lg p-6">
                <div v-if="matieres.some(m => m.nom === 'Sport')" class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="showDispenseSport"
                            @change="toggleDispenseSport"
                            class="w-5 h-5 text-indigo-600"
                        />
                        <span class="text-gray-700">Je suis dispensé(e) de sport</span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1">
                        Si vous êtes dispensé, la note de 10/20 sera automatiquement attribuée
                    </p>
                </div>

                <div class="space-y-4 mb-8">
                    <div
                        v-for="(matiere, index) in matieres"
                        :key="index"
                        class="flex items-center space-x-4 p-3 border rounded-lg hover:bg-gray-50"
                    >
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">
                                {{ matiere.nom }}
                                <span class="text-xs text-indigo-600 ml-2">Coef {{ matiere.coefficient }}</span>
                            </label>
                        </div>
                        
                        <div class="w-32">
                            <input
                                v-if="!(matiere.hasDispense && matiere.nom === 'Sport')"
                                v-model="matiere.note"
                                type="number"
                                min="0"
                                max="20"
                                step="0.25"
                                :disabled="matiere.hasDispense && matiere.nom === 'Sport'"
                                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                                :class="{ 'bg-gray-100': matiere.hasDispense && matiere.nom === 'Sport' }"
                                placeholder="Note"
                            />
                            <span v-else class="text-sm text-gray-500">Dispense (10/20)</span>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50 p-6 rounded-lg mb-6">
                    <h3 class="text-lg font-semibold text-indigo-800 mb-4">Vos résultats</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <p class="text-sm text-gray-600">Moyenne générale</p>
                            <p class="text-3xl font-bold text-indigo-600">{{ moyenne }}/20</p>
                        </div>
                        
                        <div class="bg-white p-4 rounded-lg shadow">
                            <p class="text-sm text-gray-600">Score (Moy × 2)</p>
                            <p class="text-3xl font-bold text-green-600">{{ score }}</p>
                        </div>
                        
                        <div class="bg-white p-4 rounded-lg shadow border-2 border-yellow-400">
                            <p class="text-sm text-gray-600">Score +7%</p>
                            <p class="text-3xl font-bold text-yellow-600">{{ scorePlus7 }}</p>
                            <p class="text-xs text-gray-500">Pour filières sélectives</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button
                        @click="retourSpecialites"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                    >
                        Changer de spécialité
                    </button>
                    
                    <button
                        @click="allerDashboard"
                        :disabled="!allNotesFilled || isSubmitting"
                        class="px-8 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                    >
                        <span v-if="!isSubmitting">Calculer et continuer 🚀</span>
                        <span v-else>Enregistrement...</span>
                    </button>
                </div>

                <p v-if="!allNotesFilled" class="text-sm text-red-500 mt-4 text-right">
                    Veuillez remplir toutes les notes
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.specialite-card {
    transition: all 0.2s ease;
}
</style>