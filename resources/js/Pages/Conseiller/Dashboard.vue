<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <!-- ── En-tête ──────────────────────────────────────────── -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Dashboard
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Bonjour, <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ conseiller.name }}</span>
                        </p>
                    </div>
                    <!-- Toggle thème -->
                    <button @click="toggleDark"
                            class="w-10 h-10 rounded-xl flex items-center justify-center border transition-all"
                            :class="isDark ? 'bg-gray-800 border-gray-600 text-yellow-400' : 'bg-white border-gray-200 text-gray-600'">
                        <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                </div>

                <!-- Flash message -->
                <div v-if="$page.props.flash?.success"
                     class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-xl text-sm flex items-center gap-2">
                    ✅ {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error"
                     class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-xl text-sm flex items-center gap-2">
                    ❌ {{ $page.props.flash.error }}
                </div>

                <!-- ══════════════════════════════════════════════════════
                     CONTENU DASHBOARD
                ══════════════════════════════════════════════════════ -->
                <div class="space-y-6">
                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-3xl">👨‍🎓</span>
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">Total</span>
                            </div>
                            <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ totalEtudiants ?? 0 }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Étudiants inscrits</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-3xl">🎓</span>
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Actives</span>
                            </div>
                            <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ totalFilieres ?? 0 }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Filières disponibles</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-3xl">📝</span>
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">Questionnaire</span>
                            </div>
                            <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ questionnairesCount ?? 0 }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Questionnaires envoyés</p>
                        </div>
                    </div>

                    <!-- Tableau étudiants récents -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Derniers étudiants inscrits</h2>
                            <span class="text-xs text-gray-400">5 derniers</span>
                        </div>
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Nom</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Inscription</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="e in recentEtudiants" :key="e.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-300 text-sm font-bold">
                                                {{ e.name?.charAt(0).toUpperCase() }}
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ e.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ e.email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ fmtDate(e.created_at) }}</td>
                                </tr>
                                <tr v-if="!recentEtudiants?.length">
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-400">Aucun étudiant</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    
                </div>

            </div>
        </div>
        <ChatIcon />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ChatIcon from '@/Components/ChatIcon.vue'

// ── Props ──────────────────────────────────────────────────────────────────────
const props = defineProps({
    totalEtudiants:        { type: Number, default: 0 },
    totalFilieres:         { type: Number, default: 0 },
    recentEtudiants:       { type: Array,  default: () => [] },
    conseiller:            { type: Object, default: () => ({}) },
    questionnairesCount:   { type: Number, default: 0 },
    recentQuestionnaires:  { type: Array,  default: () => [] },
})

// ── Thème ──────────────────────────────────────────────────────────────────────
const isDark = ref(false)
const toggleDark = () => {
    isDark.value = !isDark.value
    isDark.value ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', isDark.value ? 'sombre' : 'clair')
}
onMounted(() => {
    const t = localStorage.getItem('theme')
    if (t === 'sombre' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDark.value = true
        document.documentElement.classList.add('dark')
    }
})

// ── Helpers ────────────────────────────────────────────────────────────────────
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) : ''
</script>