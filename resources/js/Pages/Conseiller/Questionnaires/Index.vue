<template>
  <AuthenticatedLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-10 transition-colors">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 space-y-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
            Questionnaires envoyés
          </h1>
          <button
            @click="showForm = !showForm"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 text-white px-5 py-2.5 rounded-xl font-medium transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5"
          >
            <!-- Icône plus -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouveau questionnaire
          </button>
        </div>

        <!-- Flash message succès -->
        <div
          v-if="$page.props.flash?.success"
          class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl text-green-700 dark:text-green-300 flex items-center gap-3 animate-fadeIn"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ $page.props.flash.success }}
        </div>

        <!-- Flash message erreur -->
        <div
          v-if="$page.props.flash?.error"
          class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-300 flex items-center gap-3 animate-fadeIn"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ $page.props.flash.error }}
        </div>

        <!-- Formulaire création -->
        <transition name="slide-fade">
          <div
            v-if="showForm"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-indigo-100 dark:border-gray-700 p-6 space-y-6"
          >
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Créer un questionnaire</h2>

            <!-- Titre -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Titre</label>
              <input
                v-model="form.titre"
                type="text"
                placeholder="Ex: Bilan d'orientation S1"
                class="w-full border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              />
            </div>

            <!-- Info envoi à tous -->
            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-4 flex items-center gap-3">
              <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <p class="text-sm text-indigo-700 dark:text-indigo-300">
                Ce questionnaire sera envoyé à <strong>tous les étudiants</strong> approuvés.
              </p>
            </div>

            <!-- Questions -->
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Questions</label>
                <button
                  @click="ajouterQuestion"
                  type="button"
                  class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 text-sm font-medium hover:underline transition"
                >
                  + Ajouter une question
                </button>
              </div>

              <div
                v-for="(q, i) in form.questions"
                :key="i"
                class="border border-gray-200 dark:border-gray-600 rounded-xl p-5 space-y-3 bg-gray-50/50 dark:bg-gray-700/30"
              >
                <div class="flex justify-between items-center">
                  <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Question {{ i + 1 }}</span>
                  <button
                    @click="supprimerQuestion(i)"
                    type="button"
                    class="text-red-500 hover:text-red-700 text-sm font-medium transition"
                  >
                    Supprimer
                  </button>
                </div>
                <textarea
                  v-model="q.texte"
                  rows="2"
                  placeholder="Texte de la question..."
                  class="w-full border border-gray-200 dark:border-gray-600 rounded-xl p-3 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm resize-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                ></textarea>
                <select
                  v-model="q.type"
                  @change="onTypeChange(q)"
                  class="w-full border border-gray-200 dark:border-gray-600 rounded-xl p-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
                  <option value="text">Texte libre</option>
                  <option value="choix_unique">Choix unique</option>
                  <option value="choix_multiple">Choix multiple</option>
                </select>
                <!-- Options -->
                <div v-if="q.type !== 'text'" class="space-y-2">
                  <div v-for="(opt, oi) in q.options" :key="oi" class="flex gap-2">
                    <input
                      v-model="q.options[oi]"
                      type="text"
                      placeholder="Option..."
                      class="flex-1 border border-gray-200 dark:border-gray-600 rounded-xl p-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    />
                    <button
                      @click="q.options.splice(oi, 1)"
                      type="button"
                      class="text-red-400 hover:text-red-600 p-1"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <button
                    @click="q.options.push('')"
                    type="button"
                    class="text-indigo-500 hover:text-indigo-600 text-sm font-medium transition"
                  >
                    + Option
                  </button>
                </div>
              </div>
            </div>

            <!-- Erreur -->
            <p v-if="formError" class="text-red-500 text-sm flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ formError }}
            </p>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
              <button
                @click="showForm = false"
                type="button"
                class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-all duration-200 hover:shadow-sm"
              >
                Annuler
              </button>
              <button
                @click="envoyerQuestionnaire"
                :disabled="sending"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 text-white rounded-xl font-medium disabled:opacity-50 transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5"
              >
                <svg v-if="!sending" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                <span>{{ sending ? 'Envoi…' : 'Envoyer à tous' }}</span>
              </button>
            </div>
          </div>
        </transition>

        <!-- Liste questionnaires -->
        <div v-if="questionnaires && questionnaires.length" class="space-y-4">
          <div
            v-for="q in questionnaires"
            :key="q.id"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 sm:flex sm:items-center sm:justify-between gap-4 group hover:shadow-md transition-all duration-300 hover:-translate-y-0.5"
          >
            <div>
              <p class="font-semibold text-gray-800 dark:text-white text-lg">{{ q.titre }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                <span class="inline-flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ q.etudiant?.name }}
                </span>
                <span class="inline-flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  {{ q.questions?.length }} question(s)
                </span>
                <span class="inline-flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  {{ formatDate(q.envoye_le) }}
                </span>
              </p>
            </div>
            <div class="flex items-center gap-3 mt-4 sm:mt-0">
              <span
                :class="q.statut === 'repondu'
                  ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                  : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'"
                class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full transition-colors"
              >
                <svg v-if="q.statut === 'repondu'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ q.statut === 'repondu' ? 'Répondu' : 'En attente' }}
              </span>
              <Link
                v-if="q.statut === 'repondu'"
                :href="route('conseiller.questionnaires.resultats', q.id)"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 hover:underline transition"
              >
                Voir résultats
              </Link>
              <button
                @click="supprimer(q.id)"
                class="text-sm font-medium text-red-500 hover:text-red-700 transition"
              >
                Supprimer
              </button>
            </div>
          </div>
        </div>

        <!-- État vide -->
        <div v-else class="text-center py-20 text-gray-400 dark:text-gray-500">
          <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <p class="text-lg font-medium">Aucun questionnaire envoyé</p>
          <p class="text-sm mt-1">Créez votre premier questionnaire pour commencer.</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
// Le script reste inchangé par rapport à votre code original
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  questionnaires: Array,
  etudiants: Array,
})

const showForm = ref(false)
const sending = ref(false)
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
    year: 'numeric',
  })
}
async function envoyerQuestionnaire() {
  formError.value = ''
  if (!form.value.titre?.trim()) return (formError.value = 'Ajoutez un titre.')
  if (!form.value.questions.length) return (formError.value = 'Ajoutez au moins une question.')
  if (form.value.questions.some(q => !q.texte?.trim())) return (formError.value = 'Remplissez toutes les questions.')

  sending.value = true
  router.post(route('conseiller.questionnaires.store'), form.value, {
    onSuccess: () => {
      showForm.value = false
      form.value = { titre: '', questions: [{ texte: '', type: 'text', options: [] }] }
      formError.value = ''
    },
    onError: (errors) => {
      formError.value = errors?.message || 'Erreur lors de l\'envoi du questionnaire.'
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

<style scoped>
/* Animation pour les messages flash */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}
/* Transition pour le formulaire */
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.2s ease-in;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>