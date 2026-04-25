<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-10 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Bouton retour -->
                <div class="mb-6">
                    <Link
                        :href="route('conseiller.etudiants.index')"
                        class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Retour à la liste
                    </Link>
                </div>

                <!-- Informations de l'étudiant -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors mb-6">
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-6">
                            <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900/60 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-10 h-10 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ etudiant.name }}</h1>
                                <p class="text-gray-600 dark:text-gray-400">{{ etudiant.email }}</p>
                                <p class="text-sm text-indigo-600 dark:text-indigo-400 mt-1">
                                    Filière : {{ etudiant.filiere?.specialite || 'Non assigné' }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl flex items-center gap-3 hover:shadow-md transition-shadow">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Membre depuis</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ new Date(etudiant.created_at).toLocaleDateString('fr-FR') }}
                                    </p>
                                </div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-xl flex items-center gap-3 hover:shadow-md transition-shadow">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Moyennes</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ moyennes.length }}</p>
                                </div>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-xl flex items-center gap-3 hover:shadow-md transition-shadow">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Favoris</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ favoris.length }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommandations intelligentes (amélioré avec couleurs et animations) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors mb-6 group">
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-6 h-6 text-indigo-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Recommandations intelligentes
                            </h2>
                            <span class="text-xs font-medium px-3 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                Mixte
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Classement mixte (questionnaire, moyenne bac, seuils « dernier orienté » importés). Même vue que l'onglet Recommandations du chat.
                        </p>
                        <div class="bg-gradient-to-br from-indigo-50/50 to-blue-50/50 dark:from-indigo-900/10 dark:to-blue-900/10 rounded-xl p-1">
                            <RecoDashboardPanel :data="recoData" :loading="recoLoading" />
                        </div>
                    </div>
                </div>

                <!-- Profil académique & Score T (couleurs adaptées au thème) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors mb-6">
                    <div class="p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l0 6.055" />
                            </svg>
                            Profil académique & Score T
                        </h2>

                        <div v-if="moyennes.length === 0" class="text-center py-12 text-gray-500">
                            <svg class="mx-auto h-12 w-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Aucune moyenne enregistrée
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="moyenne in moyennes"
                                :key="moyenne.id"
                                class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1"
                            >
                                <!-- Bandeau de spécialité -->
                                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white leading-tight">
                                            {{ moyenne.specialite }}
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ new Date(moyenne.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                        </p>
                                    </div>
                                    <span class="text-xs font-mono text-gray-400 dark:text-gray-500">#{{ moyenne.id }}</span>
                                </div>

                                <!-- Corps -->
                                <div class="p-5 space-y-5">
                                    <!-- Score T principal -->
                                    <div class="flex items-end justify-between">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Score T</span>
                                            <div class="flex items-baseline gap-1 mt-0.5">
                                                <span class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ moyenne.score }}</span>
                                                <span class="text-sm text-gray-500">/200</span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Moy. brute</span>
                                            <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ moyenne.moyenne }}/20</p>
                                        </div>
                                    </div>

                                    <!-- Barre de progression -->
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-gray-500">Niveau</span>
                                            <span class="font-medium" :class="scoreColor(moyenne.score)">{{ scoreLabel(moyenne.score) }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                            <div
                                                class="h-2 rounded-full transition-all duration-700"
                                                :class="scoreBarColor(moyenne.score)"
                                                :style="{ width: (moyenne.score / 200 * 100) + '%' }"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Score +7% -->
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                            </svg>
                                            <span class="text-xs text-gray-500">Avec +7%</span>
                                        </div>
                                        <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ moyenne.score_plus_7 }}</span>
                                    </div>
                                </div>

                                <!-- Bouton détails -->
                                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 rounded-b-2xl border-t border-gray-100 dark:border-gray-700">
                                    <button
                                        @click="openDetails(moyenne)"
                                        class="w-full text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition"
                                    >
                                        Voir le détail des notes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filières favorites -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors">
                    <div class="p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            Filières favorites
                        </h2>

                        <div v-if="favoris.length === 0" class="text-center py-12 text-gray-500">
                            <svg class="mx-auto h-12 w-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            Aucune filière favorite
                        </div>

                        <div v-else class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">الاختصاص الاكبر</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">الرمز</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">المؤسسة والجامعة</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">نوع الباكالوريا</th>
                                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">صيغة احتساب النقاط</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr
                                        v-for="filiere in favoris"
                                        :key="filiere.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                    >
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ filiere.specialite || filiere.nom }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ filiere.code || '-' }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ filiere.universite || '-' }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ filiere.type_bac || '-' }}</td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ filiere.formule || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal détails des moyennes -->
        <transition name="fade">
            <div v-if="showDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="closeDetails">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] overflow-y-auto">
                    <div class="sticky top-0 bg-indigo-600 dark:bg-indigo-700 px-6 py-5 rounded-t-2xl z-10">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-xl font-bold text-white">{{ selectedMoyenne?.specialite }}</h2>
                                <p class="text-indigo-100 text-sm mt-0.5">
                                    {{ selectedMoyenne ? new Date(selectedMoyenne.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '' }}
                                </p>
                            </div>
                            <button @click="closeDetails" class="text-white hover:bg-white/20 rounded-full p-1 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-xl text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Moyenne générale</p>
                                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ selectedMoyenne?.moyenne }}/20</p>
                            </div>
                            <div class="bg-emerald-50 dark:bg-emerald-900/20 p-4 rounded-xl text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Score (Moy × 2)</p>
                                <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ selectedMoyenne?.score }}</p>
                            </div>
                            <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Score +7%</p>
                                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ selectedMoyenne?.score_plus_7 }}</p>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Détail des notes
                        </h3>

                        <div class="space-y-3">
                            <div
                                v-for="(matiere, index) in selectedMoyenne?.matieres"
                                :key="index"
                                class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                            >
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="font-medium text-gray-800 dark:text-white">{{ matiere.nom }}</span>
                                        <span class="text-xs text-indigo-600 dark:text-indigo-400 ml-2">Coef {{ matiere.coefficient }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="text-xl font-bold"
                                            :class="{
                                                'text-green-600': parseFloat(matiere.note) >= 12,
                                                'text-orange-500': parseFloat(matiere.note) >= 10 && parseFloat(matiere.note) < 12,
                                                'text-red-600': parseFloat(matiere.note) < 10
                                            }"
                                        >
                                            {{ matiere.note }}/20
                                        </span>
                                        <div class="text-xs text-gray-500">
                                            Poids : {{ (matiere.note * matiere.coefficient).toFixed(2) }} pts
                                        </div>
                                    </div>
                                </div>

                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 mt-2">
                                    <div
                                        class="h-1.5 rounded-full transition-all duration-500"
                                        :class="{
                                            'bg-green-500': parseFloat(matiere.note) >= 12,
                                            'bg-orange-500': parseFloat(matiere.note) >= 10 && parseFloat(matiere.note) < 12,
                                            'bg-red-500': parseFloat(matiere.note) < 10
                                        }"
                                        :style="{ width: (parseFloat(matiere.note) / 20 * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row gap-3 text-sm">
                            <div class="flex-1">
                                <span class="text-gray-500 dark:text-gray-400">Total des coefficients :</span>
                                <span class="font-medium text-gray-900 dark:text-white ml-1">
                                    {{ selectedMoyenne?.matieres?.reduce((sum, m) => sum + m.coefficient, 0) }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <span class="text-gray-500 dark:text-gray-400">Nombre de matières :</span>
                                <span class="font-medium text-gray-900 dark:text-white ml-1">{{ selectedMoyenne?.matieres?.length }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-700/80 backdrop-blur-md px-6 py-4 rounded-b-2xl flex justify-end border-t dark:border-gray-600">
                        <button @click="closeDetails" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-all duration-200 hover:shadow-lg">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import RecoDashboardPanel from '@/Components/RecoDashboardPanel.vue'

const props = defineProps({
    etudiant: Object,
    moyennes: Array,
    favoris: Array
})

const recoData = ref(null)
const recoLoading = ref(true)

onMounted(async () => {
    try {
        const res = await fetch(`/api/profil/etudiant/${props.etudiant.id}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        if (res.ok) {
            recoData.value = await res.json()
        }
    } catch (e) {
        console.error('reco profil etudiant:', e)
    } finally {
        recoLoading.value = false
    }
})

const selectedMoyenne = ref(null)
const showDetailsModal = ref(false)

function openDetails(moyenne) {
    selectedMoyenne.value = moyenne
    showDetailsModal.value = true
}

function closeDetails() {
    showDetailsModal.value = false
    selectedMoyenne.value = null
}

// ── Helpers pour le score T (couleurs adaptées au thème indigo) ────
const scoreColor = (score) => {
    if (score >= 120) return 'text-emerald-600 dark:text-emerald-400'
    if (score >= 100) return 'text-indigo-600 dark:text-indigo-400'
    return 'text-red-500'
}
const scoreBarColor = (score) => {
    if (score >= 120) return 'bg-emerald-500'
    if (score >= 100) return 'bg-indigo-500'
    return 'bg-red-500'
}
const scoreLabel = (score) => {
    if (score >= 120) return 'Très bon'
    if (score >= 100) return 'Correct'
    return 'Faible'
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>