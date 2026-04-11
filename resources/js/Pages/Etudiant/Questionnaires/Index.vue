<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-10">
            <div class="max-w-3xl mx-auto px-4 space-y-6">

                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📝 Mes questionnaires</h1>

                <div v-if="questionnaires.length" class="space-y-4">
                    <div v-for="q in questionnaires" :key="q.id"
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white">{{ q.titre }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                De {{ q.conseiller?.name }} •
                                {{ q.questions?.length }} question(s) •
                                Reçu le {{ formatDate(q.envoye_le) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span :class="q.statut === 'repondu'
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'"
                                class="text-xs font-semibold px-3 py-1 rounded-full">
                                {{ q.statut === 'repondu' ? '✅ Répondu' : '⏳ À répondre' }}
                            </span>
                            <Link v-if="q.statut !== 'repondu'"
                                :href="route('etudiant.questionnaires.repondre', q.id)"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded-lg transition">
                                Répondre
                            </Link>
                            <span v-else class="text-sm text-gray-400">Terminé</span>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 text-gray-400">
                    <div class="text-5xl mb-3">📭</div>
                    <p>Aucun questionnaire reçu pour le moment.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ questionnaires: Array })

function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>