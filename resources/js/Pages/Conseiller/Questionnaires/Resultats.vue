<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-10">
            <div class="max-w-4xl mx-auto px-4 space-y-6">

                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            📊 {{ questionnaire.titre }}
                        </h1>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                            👤 {{ questionnaire.etudiant?.name }} •
                            Répondu le {{ formatDate(questionnaire.repondu_le) }}
                        </p>
                    </div>
                    <Link :href="route('conseiller.questionnaires.index')"
                        class="text-sm text-indigo-600 hover:underline">
                        ← Retour
                    </Link>
                </div>

                <div v-for="(q, i) in questionnaire.questions" :key="q.id"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 text-sm font-bold flex items-center justify-center flex-shrink-0">
                            {{ i + 1 }}
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800 dark:text-white mb-3">{{ q.texte }}</p>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-gray-700 dark:text-gray-200 text-sm whitespace-pre-wrap">
                                    {{ getReponse(q.id) || '— Pas de réponse —' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export PDF -->
                <div class="flex justify-end gap-3">
                    <button @click="exportPDF"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                        📄 Exporter en PDF
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
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
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}

async function exportPDF() {
    // Importer html2pdf.js dynamiquement
    const { default: html2pdf } = await import('html2pdf.js')
    
    // Créer le contenu HTML pour le PDF
    const content = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>${props.questionnaire.titre}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 40px;
                    line-height: 1.6;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #4f46e5;
                    padding-bottom: 20px;
                }
                .header h1 {
                    color: #4f46e5;
                    margin: 0;
                }
                .header p {
                    color: #666;
                    margin: 5px 0;
                }
                .question {
                    margin-bottom: 25px;
                    page-break-inside: avoid;
                }
                .question-title {
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 8px;
                    padding: 8px;
                    background: #f3f4f6;
                    border-left: 4px solid #4f46e5;
                }
                .reponse {
                    padding: 10px 15px;
                    background: #f9fafb;
                    border-radius: 8px;
                    margin-top: 5px;
                }
                .reponse-label {
                    font-weight: bold;
                    color: #4f46e5;
                    font-size: 12px;
                    margin-bottom: 5px;
                }
                .reponse-content {
                    color: #333;
                    white-space: pre-wrap;
                }
                .footer {
                    margin-top: 50px;
                    text-align: center;
                    font-size: 12px;
                    color: #999;
                    border-top: 1px solid #ddd;
                    padding-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>📊 ${props.questionnaire.titre}</h1>
                <p>Étudiant : ${props.questionnaire.etudiant?.name || 'N/A'}</p>
                <p>Date de réponse : ${formatDate(props.questionnaire.repondu_le)}</p>
            </div>
            
            ${props.questionnaire.questions.map((q, i) => `
                <div class="question">
                    <div class="question-title">
                        Question ${i + 1} : ${q.texte}
                    </div>
                    <div class="reponse">
                        <div class="reponse-label">📝 Réponse :</div>
                        <div class="reponse-content">${getReponse(q.id) || '— Pas de réponse —'}</div>
                    </div>
                </div>
            `).join('')}
            
            <div class="footer">
                Document généré par Orienta - ${new Date().toLocaleDateString('fr-FR')}
            </div>
        </body>
        </html>
    `
    
    // Créer un élément temporaire pour le PDF
    const element = document.createElement('div')
    element.innerHTML = content
    document.body.appendChild(element)
    
    // Options pour html2pdf
    const options = {
        margin: [10, 10, 10, 10],
        filename: `${props.questionnaire.titre}_${props.questionnaire.etudiant?.name || 'reponses'}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }
    
    // Générer le PDF
    await html2pdf().set(options).from(element).save()
    
    // Supprimer l'élément temporaire
    document.body.removeChild(element)
}
</script>