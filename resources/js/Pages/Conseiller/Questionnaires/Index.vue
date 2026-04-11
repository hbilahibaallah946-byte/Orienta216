<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-10">
            <div class="max-w-5xl mx-auto px-4 space-y-8">

                <!-- Header -->
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        📋 Questionnaires envoyés
                    </h1>
                    <button @click="showForm = !showForm"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        + Nouveau questionnaire
                    </button>
                </div>

                <!-- Flash message succès -->
                <div v-if="$page.props.flash?.success"
                    class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">
                    ✅ {{ $page.props.flash.success }}
                </div>

                <!-- Flash message erreur -->
                <div v-if="$page.props.flash?.error"
                    class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                    ❌ {{ $page.props.flash.error }}
                </div>

                <!-- Formulaire création -->
                <div v-if="showForm"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-indigo-100 dark:border-gray-700 space-y-6">
                    <h2 class="text-lg font-semibold dark:text-white">Créer un questionnaire</h2>

                    <!-- Titre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titre</label>
                        <input v-model="form.titre" type="text" placeholder="Ex: Bilan d'orientation S1"
                            class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>

                    <!-- Info envoi à tous les étudiants -->
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-3">
                        <p class="text-sm text-indigo-700 dark:text-indigo-300 flex items-center gap-2">
                            📨 Ce questionnaire sera envoyé à <strong>TOUS les étudiants</strong> approuvés
                        </p>
                    </div>

                    <!-- Questions -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Questions</label>
                            <button @click="ajouterQuestion" type="button"
                                class="text-indigo-600 text-sm hover:underline">+ Ajouter une question</button>
                        </div>

                        <div v-for="(q, i) in form.questions" :key="i"
                            class="border dark:border-gray-600 rounded-lg p-4 space-y-3 bg-gray-50 dark:bg-gray-700/50">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-indigo-600">Question {{ i + 1 }}</span>
                                <button @click="supprimerQuestion(i)" type="button"
                                    class="text-red-500 text-sm hover:text-red-700">Supprimer</button>
                            </div>
                            <textarea v-model="q.texte" rows="2" placeholder="Texte de la question..."
                                class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm resize-none" />
                            <select v-model="q.type" @change="onTypeChange(q)"
                                class="w-full border rounded-lg p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                <option value="text">Texte libre</option>
                                <option value="choix_unique">Choix unique</option>
                                <option value="choix_multiple">Choix multiple</option>
                            </select>
                            <!-- Options -->
                            <div v-if="q.type !== 'text'" class="space-y-2">
                                <div v-for="(opt, oi) in q.options" :key="oi" class="flex gap-2">
                                    <input v-model="q.options[oi]" type="text" placeholder="Option..."
                                        class="flex-1 border rounded-lg p-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                                    <button @click="q.options.splice(oi, 1)" type="button"
                                        class="text-red-400 hover:text-red-600">✕</button>
                                </div>
                                <button @click="q.options.push('')" type="button"
                                    class="text-indigo-500 text-sm hover:underline">+ Option</button>
                            </div>
                        </div>
                    </div>

                    <!-- Erreur -->
                    <p v-if="formError" class="text-red-500 text-sm">{{ formError }}</p>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3">
                        <button @click="showForm = false" type="button"
                            class="px-4 py-2 rounded-lg border dark:border-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            Annuler
                        </button>
                        <button @click="envoyerQuestionnaire" :disabled="sending"
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium disabled:opacity-50 transition">
                            {{ sending ? 'Envoi…' : '📤 Envoyer à tous' }}
                        </button>
                    </div>
                </div>

                <!-- Liste questionnaires -->
                <div v-if="props.questionnaires && props.questionnaires.length" class="space-y-4">
                    <div v-for="q in props.questionnaires" :key="q.id"
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white">{{ q.titre }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                👤 {{ q.etudiant?.name }} •
                                {{ q.questions?.length }} question(s) •
                                📅 {{ formatDate(q.envoye_le) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span :class="q.statut === 'repondu'
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'"
                                class="text-xs font-semibold px-3 py-1 rounded-full">
                                {{ q.statut === 'repondu' ? '✅ Répondu' : '⏳ En attente' }}
                            </span>
                            <Link v-if="q.statut === 'repondu'"
                                :href="route('conseiller.questionnaires.resultats', q.id)"
                                class="text-sm text-indigo-600 hover:underline">
                                Voir résultats
                            </Link>
                            <button @click="supprimer(q.id)"
                                class="text-sm text-red-500 hover:text-red-700">Supprimer</button>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 text-gray-400">
                    <div class="text-5xl mb-3">📭</div>
                    <p>Aucun questionnaire envoyé pour le moment.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    questionnaires: Array,
    etudiants: Array,
})

const showForm = ref(false)
const sending  = ref(false)
const formError = ref('')

const form = ref({
    titre: '',
    questions: [{ texte: '', type: 'text', options: [] }],
})

function ajouterQuestion() {
    form.value.questions.push({ texte: '', type: 'text', options: [] })
}

function supprimerQuestion(i) {
    form.value.questions.splice(i, 1)
}

function onTypeChange(q) {
    if (q.type === 'text') {
        q.options = []
    } else if (!q.options || !q.options.length) {
        q.options = ['', '']
    }
}

function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('fr-FR', {
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric'
    })
}

async function envoyerQuestionnaire() {
    formError.value = ''
    
    // Validation
    if (!form.value.titre || !form.value.titre.trim()) {
        formError.value = 'Ajoutez un titre.'
        return
    }
    if (!form.value.questions.length) {
        formError.value = 'Ajoutez au moins une question.'
        return
    }
    if (form.value.questions.some(q => !q.texte || !q.texte.trim())) {
        formError.value = 'Remplissez toutes les questions.'
        return
    }

    sending.value = true
    
    router.post(route('conseiller.questionnaires.store'), form.value, {
        onSuccess: () => {
            showForm.value = false
            form.value = { 
                titre: '', 
                questions: [{ texte: '', type: 'text', options: [] }] 
            }
            formError.value = ''
        },
        onError: (errors) => {
            console.error('Erreur:', errors)
            if (errors && errors.message) {
                formError.value = errors.message
            } else {
                formError.value = 'Erreur lors de l\'envoi du questionnaire.'
            }
        },
        onFinish: () => {
            sending.value = false
        },
    })
}

function supprimer(id) {
    if (confirm('Supprimer ce questionnaire ?')) {
        router.delete(route('conseiller.questionnaires.destroy', id))
    }
}
</script>