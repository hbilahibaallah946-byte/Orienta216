<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-10">
            <div class="max-w-2xl mx-auto px-4 space-y-5">

                <Link :href="route('etudiant.questionnaires.index')"
                      class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    ← Retour
                </Link>

                <div class="text-center">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📝 {{ questionnaire.titre }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ questionnaire.questions?.length }} question(s)</p>
                </div>

                <!-- Flash Inertia -->
                <div v-if="$page.props.flash?.success"
                     class="p-4 bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-xl flex items-center gap-2">
                    ✅ <span class="text-green-700 dark:text-green-300 font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <!-- Erreurs Inertia -->
                <div v-if="Object.keys(form.errors).length"
                     class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-xl">
                    <p class="text-red-700 dark:text-red-300 font-medium mb-1">❌ Erreurs :</p>
                    <ul class="text-sm text-red-600 list-disc list-inside">
                        <li v-for="(e, k) in form.errors" :key="k">{{ e }}</li>
                    </ul>
                </div>

                <div v-if="dejaRepondu"
                     class="p-3 bg-green-50 dark:bg-green-900/30 border border-green-300 rounded-xl text-center text-green-700 dark:text-green-300 text-sm font-medium">
                    ✅ Déjà répondu — vous pouvez modifier vos réponses ci-dessous.
                </div>

                <!-- Questions -->
                <div v-for="(q, idx) in questionnaire.questions" :key="q.id"
                     class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-sm font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                            {{ idx + 1 }}
                        </div>
                        <div class="flex-1">
                            <span v-if="q.categorie"
                                  class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-2"
                                  :class="q.categorie === 'competence' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                        : q.categorie === 'preference' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'
                                        : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'">
                                {{ { competence: '🛠️ Compétence', preference: '⚙️ Préférence' }[q.categorie] ?? '❤️ Intérêt' }}
                            </span>

                            <p class="font-semibold text-gray-800 dark:text-gray-100 mb-3">{{ q.texte }}</p>

                            <textarea v-if="q.type === 'text'"
                                      v-model="answers[q.id]" rows="3"
                                      class="w-full border border-gray-200 dark:border-gray-600 rounded-lg p-3 text-sm bg-gray-50 dark:bg-gray-700 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 outline-none resize-none"
                                      placeholder="Votre réponse…"/>

                            <div v-else-if="q.type === 'choix_unique'" class="space-y-2">
                                <label v-for="opt in q.options" :key="opt" class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" :name="`q_${q.id}`" :value="opt" v-model="answers[q.id]"
                                           class="text-indigo-600"/>
                                    <span class="text-sm dark:text-gray-200">{{ opt }}</span>
                                </label>
                            </div>

                            <div v-else-if="q.type === 'choix_multiple'" class="space-y-2">
                                <label v-for="opt in q.options" :key="opt" class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" :value="opt"
                                           :checked="(multipleAnswers[q.id] ?? []).includes(opt)"
                                           @change="toggleMultiple(q.id, opt)"
                                           class="text-indigo-600"/>
                                    <span class="text-sm dark:text-gray-200">{{ opt }}</span>
                                </label>
                            </div>

                            <p v-if="reponsesExistantes?.[q.id]"
                               class="text-xs text-indigo-400 mt-1.5">✏️ Réponse précédente chargée</p>
                        </div>
                    </div>
                </div>

                <!-- Progression -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mb-2">
                        <span>Questions répondues</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ answeredCount }} / {{ questionnaire.questions?.length }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500"
                             :style="{ width: progressPercent + '%' }"></div>
                    </div>
                </div>

                <!-- Bouton -->
                <div class="flex justify-center pb-8">
                    <button @click="submit"
                            :disabled="form.processing || answeredCount === 0"
                            class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl shadow-lg transition-all hover:scale-105 active:scale-95 flex items-center gap-3">
                        <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        {{ form.processing ? 'Envoi en cours…' : 'Envoyer mes réponses' }}
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    questionnaire:      { type: Object, required: true },
    reponsesExistantes: { type: Object, default: () => ({}) },
})

const dejaRepondu     = computed(() => props.questionnaire.statut === 'repondu')
const answers         = ref({ ...props.reponsesExistantes })
const multipleAnswers = ref({})

// Init choix multiples depuis existantes
props.questionnaire.questions?.forEach(q => {
    if (q.type === 'choix_multiple' && props.reponsesExistantes[q.id]) {
        multipleAnswers.value[q.id] = props.reponsesExistantes[q.id].split(', ').filter(Boolean)
    }
})

// useForm gère CSRF + headers + redirect + flash automatiquement
const form = useForm({ reponses: [] })

const answeredCount = computed(() =>
    (props.questionnaire.questions ?? []).filter(q =>
        q.type === 'choix_multiple'
            ? (multipleAnswers.value[q.id] ?? []).length > 0
            : answers.value[q.id]?.toString().trim()
    ).length
)

const progressPercent = computed(() => {
    const t = props.questionnaire.questions?.length ?? 0
    return t ? (answeredCount.value / t) * 100 : 0
})

function toggleMultiple(qId, opt) {
    if (!multipleAnswers.value[qId]) multipleAnswers.value[qId] = []
    const idx = multipleAnswers.value[qId].indexOf(opt)
    idx === -1 ? multipleAnswers.value[qId].push(opt) : multipleAnswers.value[qId].splice(idx, 1)
    answers.value[qId] = multipleAnswers.value[qId].join(', ')
}

function submit() {
    const reponses = (props.questionnaire.questions ?? [])
        .map(q => {
            const val = q.type === 'choix_multiple'
                ? (multipleAnswers.value[q.id] ?? []).join(', ')
                : (answers.value[q.id]?.toString().trim() ?? '')
            return val ? { question_id: q.id, reponse: val } : null
        })
        .filter(Boolean)

    if (!reponses.length) return

    form.reponses = reponses
    form.post(route('etudiant.questionnaires.soumettre', props.questionnaire.id), {
        preserveScroll: true,
        onSuccess: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
    })
}
</script>