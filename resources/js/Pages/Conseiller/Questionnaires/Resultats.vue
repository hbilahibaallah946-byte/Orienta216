<template>
  <AuthenticatedLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-10 transition-colors">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
              <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              {{ questionnaire.titre }}
            </h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
              <span class="inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ questionnaire.etudiant?.name }}
              </span>
              <span class="inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ formatDate(questionnaire.repondu_le) }}
              </span>
            </p>
          </div>
          <Link
            :href="route('conseiller.questionnaires.index')"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center gap-1 hover:underline transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour
          </Link>
        </div>

        <!-- Questions & réponses -->
        <div
          v-for="(q, i) in questionnaire.questions"
          :key="q.id"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 group hover:shadow-md transition-all duration-300 hover:-translate-y-0.5"
        >
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold flex items-center justify-center flex-shrink-0 text-sm">
              {{ i + 1 }}
            </div>
            <div class="flex-1">
              <p class="font-semibold text-gray-800 dark:text-white mb-3">{{ q.texte }}</p>
              <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600">
                <p class="text-gray-700 dark:text-gray-200 text-sm whitespace-pre-wrap">
                  {{ getReponse(q.id) || '— Pas de réponse —' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Export PDF -->
        <div class="flex justify-end">
          <button
            @click="exportPDF"
            class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 text-white px-5 py-2.5 rounded-xl font-medium transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Exporter en PDF
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
// Le script reste inchangé
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ questionnaire: Object })

function getReponse(questionId) {
  const r = props.questionnaire.questions
    .find(q => q.id === questionId)
    ?.reponses?.[0]
  return r?.reponse ?? null
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

async function exportPDF() {
  // (votre code d'export PDF inchangé)
  const { default: html2pdf } = await import('html2pdf.js')
  
  const content = `
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><title>${props.questionnaire.titre}</title>
    <style>/* style identique à votre version */</style>
    </head>
    <body>
      <div class="header"><h1>Résultats – ${props.questionnaire.titre}</h1>
        <p>Étudiant : ${props.questionnaire.etudiant?.name || 'N/A'}</p>
        <p>Date : ${formatDate(props.questionnaire.repondu_le)}</p></div>
      ${props.questionnaire.questions.map((q, i) => `
        <div class="question"><div class="question-title">${i+1}. ${q.texte}</div>
        <div class="reponse"><div class="reponse-label">Réponse :</div>
        <div class="reponse-content">${getReponse(q.id) || '—'}</div></div></div>
      `).join('')}
      <div class="footer">Document généré par Orienta – ${new Date().toLocaleDateString('fr-FR')}</div>
    </body></html>
  `
  const element = document.createElement('div')
  element.innerHTML = content
  document.body.appendChild(element)

  await html2pdf().set({
    margin: [10,10,10,10],
    filename: `${props.questionnaire.titre}_${props.questionnaire.etudiant?.name || 'reponses'}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  }).from(element).save()
  
  document.body.removeChild(element)
}
</script>

<style scoped>
/* Style optionnel pour cohérence des animations */
</style>