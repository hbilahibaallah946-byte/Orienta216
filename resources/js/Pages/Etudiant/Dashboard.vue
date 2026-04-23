<template>
    <div class="min-h-screen flex bg-gray-100 dark:bg-gray-900">

        <!-- ── Sidebar ──────────────────────────────────────────────── -->
        <div :class="[
                'fixed inset-y-0 left-0 bg-indigo-700 text-white w-64 p-6 space-y-2 transform transition-transform duration-300 z-40',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'md:translate-x-0 md:relative md:z-0'
             ]">
            <h2 class="text-2xl font-bold mb-6">🎓 {{ t('brand_title') }}</h2>
            <nav class="space-y-1">
                <button v-for="tab in tabs" :key="tab.id"
                        @click="activeTab = tab.id"
                        class="w-full text-left py-2.5 px-4 rounded-xl transition relative flex items-center gap-2"
                        :class="activeTab === tab.id ? 'bg-white/20 font-semibold' : 'hover:bg-white/10'">
                    <span>{{ tab.icon }}</span>{{ tab.label }}
                    <span v-if="tab.id === 'favoris' && favoriteCount"
                          class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        {{ favoriteCount }}
                    </span>
                </button>

                <!-- Boutons de navigation externe -->
                <button @click="goToQuestionnaires"
                        class="w-full text-left py-2.5 px-4 rounded-xl bg-white/20 hover:bg-white/30 transition mt-2 flex items-center gap-2">
                    📝 {{ t('external_questionnaires') }}
                </button>
                <button @click="goToCalcul"
                        class="w-full text-left py-2.5 px-4 rounded-xl bg-white/20 hover:bg-white/30 transition mt-1 flex items-center gap-2">
                    🧮 {{ t('external_calcul') }}
                </button>
                <button @click="goToPrivateUniversities"
                        class="w-full text-left py-2.5 px-4 rounded-xl bg-white/20 hover:bg-white/30 transition mt-1 flex items-center gap-2">
                    🏛️ {{ t('external_private_universities') }}
                </button>
            </nav>

            <button @click="logout"
                    class="absolute bottom-6 left-6 right-6 py-2 px-4 bg-red-600 rounded-xl hover:bg-red-700 transition text-sm font-medium">
                {{ t('logout') }}
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
    {{ t('criteria_label') }}
  </label>
  <input
    v-model="criteresInput"
    type="text"
    :placeholder="t('criteria_placeholder')"
    class="w-full border rounded-lg p-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
  />
  <p class="text-xs text-gray-400 mt-1">
    {{ t('criteria_help') }}
  </p>
</div>
                <input v-model="search" type="text"
                       :placeholder="t('search_placeholder')"
                       class="w-full max-w-md p-3 border rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"/>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                    <h2 class="text-xl font-bold mb-4 text-indigo-700 dark:text-indigo-400">{{ t('available_filieres') }}</h2>
                    <div v-if="!props.filieres?.length" class="text-gray-400 text-center py-8">{{ t('empty_filieres') }}</div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full border-collapse text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_filiere') }}</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_code') }}</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_etablissement') }}</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_parcours') }}</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_type_bac') }}</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_formule') }}</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_capacite') }}</th>
                                    <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_score_dernier') }}</th>
                                    <th class="p-3 text-center">❤️</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(critGroup, cIdx) in groupedFilieres" :key="`crit-${cIdx}-${critGroup.label}`">
                                    <tr class="bg-indigo-50/80 dark:bg-indigo-900/30 border-t border-indigo-200 dark:border-indigo-700">
                                        <td colspan="9" class="p-3 font-semibold text-indigo-800 dark:text-indigo-200"
                                            :dir="isArabicText(critGroup.label) ? 'rtl' : 'ltr'">
                                            {{ critGroup.label }}
                                        </td>
                                    </tr>
                                    <template v-for="(licGroup, lIdx) in critGroup.licences" :key="`lic-${cIdx}-${lIdx}-${licGroup.label}`">
                                        <tr v-for="(f, rIdx) in licGroup.rows"
                                            :key="`f-${f.id}`"
                                            class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                            <td v-if="rIdx === 0"
                                                :rowspan="licGroup.rows.length"
                                                class="p-3 align-top font-semibold dark:text-white bg-gray-50/60 dark:bg-gray-800/50"
                                                :dir="isArabicText(licGroup.label) ? 'rtl' : 'ltr'">
                                                {{ licGroup.label }}
                                            </td>
                                            <td class="p-3 dark:text-white">{{ f.code || '-' }}</td>
                                            <td class="p-3 dark:text-white" :dir="isArabicText(f.universite) ? 'rtl' : 'ltr'">{{ f.universite || '-' }}</td>
                                            <td class="p-3 dark:text-white" :dir="isArabicText(f.specialite || f.nom) ? 'rtl' : 'ltr'">{{ f.specialite || f.nom || '-' }}</td>
                                            <td class="p-3 dark:text-white" :dir="isArabicText(f.type_bac) ? 'rtl' : 'ltr'">{{ f.type_bac || '-' }}</td>
                                            <td class="p-3 dark:text-white">{{ f.formule || '-' }}</td>
                                            <td class="p-3 dark:text-white text-center">
                                                <span v-if="f.capacite" class="text-gray-700 dark:text-gray-300">{{ f.capacite }}</span>
                                                <span v-else class="text-gray-400">-</span>
                                            </td>
                                            <td class="p-3 dark:text-white">
                                                <span v-if="f.score_dernier_oriente_2025" class="font-semibold text-emerald-600 dark:text-emerald-400">{{ f.score_dernier_oriente_2025 }}</span>
                                                <span v-else class="text-gray-400">-</span>
                                            </td>
                                            <td class="p-3 text-center">
                                                <button @click="toggleFav(f.id)"
                                                        class="text-xl transition hover:scale-110"
                                                        :class="isFav(f.id) ? 'text-red-500' : 'text-gray-300 hover:text-red-400'">❤️</button>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── FAVORIS ─────────────────────────────────────────── -->
            <div v-if="activeTab === 'favoris'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                <h2 class="text-xl font-bold mb-4 text-indigo-700 dark:text-indigo-400">{{ t('favorites_title') }} ❤️</h2>
                <div v-if="!favFilieresData.length" class="text-gray-400 text-center py-8">{{ t('empty_favorites') }}</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_specialite') }}</th>
                                <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_universite') }}</th>
                                <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_type_bac') }}</th>
                                <th class="p-3 text-left text-gray-600 dark:text-gray-300">{{ t('th_score_dernier') }}</th>
                                <th class="p-3 text-center">{{ t('th_action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="f in favFilieresData" :key="f.id"
                                class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="p-3 dark:text-white">{{ f.specialite || f.nom }}</td>
                                <td class="p-3 dark:text-white">{{ f.universite || '-' }}</td>
                                <td class="p-3 dark:text-white">{{ f.type_bac || '-' }}</td>
                                <td class="p-3 dark:text-white">{{ f.score_dernier_oriente_2025 || '-' }}</td>
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
                <h2 class="text-xl font-bold mb-4 text-indigo-700 dark:text-indigo-400">{{ t('averages_title') }}</h2>
                <div v-if="moyennesStore.loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>
                <div v-else-if="!moyennesStore.moyennes.length" class="text-gray-400 text-center py-8">
                    {{ t('empty_averages') }}
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
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('average_label') }}</span>
                                <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ m.moyenne }}/20</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: (m.moyenne / 20 * 100) + '%' }"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t dark:border-gray-600 text-xs">
                                <div><p class="text-gray-400">{{ t('score_x2') }}</p><p class="font-semibold text-green-600">{{ m.score }}</p></div>
                                <div><p class="text-gray-400">{{ t('score_plus_7') }}</p><p class="font-semibold text-yellow-600">{{ m.score_plus_7 }}</p></div>
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
                                <p class="text-xs text-gray-500">{{ t('average_label') }}</p>
                                <p class="text-2xl font-bold text-indigo-600">{{ selectedM?.moyenne }}/20</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-xl text-center">
                                <p class="text-xs text-gray-500">{{ t('score_x2') }}</p>
                                <p class="text-2xl font-bold text-green-600">{{ selectedM?.score }}</p>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-xl text-center">
                                <p class="text-xs text-gray-500">{{ t('score_plus_7') }}</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ selectedM?.score_plus_7 }}</p>
                            </div>
                        </div>
                        <div v-for="(mat, i) in selectedM?.matieres" :key="i"
                             class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium text-sm text-gray-800 dark:text-white">{{ mat.nom }}</span>
                                    <span class="text-xs text-indigo-500 ml-2">{{ t('coef') }} {{ mat.coefficient }}</span>
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
                            {{ t('close') }}
                        </button>
                    </div>
                </div>
            </div>
<!-- ── PARAMÈTRES ────────────────────────────────────────── -->
<div v-if="activeTab === 'parametres'" class="max-w-3xl mx-auto">

    <!-- Carte principale -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">

        <!-- Header bleu -->
        <div class="bg-gradient-to-r from-indigo-800 via-indigo-600 to-indigo-500 px-6 py-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-xl flex-shrink-0">⚙️</div>
            <div>
                <h2 class="text-lg font-semibold text-white">{{ t('settings_title') }}</h2>
                <p class="text-indigo-200 text-xs mt-0.5">{{ t('settings_subtitle') }}</p>
            </div>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4">

            <!-- Langue -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-sm flex-shrink-0">🌐</div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ t('language_title') }}</h3>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="lang in languageOptions" :key="lang.value"
                            @click="changeLanguage(lang.value)"
                            class="px-4 py-2 rounded-lg border text-sm font-medium transition"
                            :class="selectedLanguage === lang.value
                                ? 'bg-indigo-600 border-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-gray-600 hover:border-indigo-300'"
                        >{{ lang.label }}</button>
                    </div>
                </div>
            </div>

            <!-- Thème -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center text-sm flex-shrink-0">🎨</div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ t('theme_title') }}</h3>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="setTheme('light')"
                            class="px-4 py-2 rounded-lg border text-sm font-medium transition"
                            :class="selectedTheme === 'light'
                                ? 'bg-indigo-600 border-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-gray-600'"
                        >☀️ {{ t('theme_light') }}</button>
                        <button
                            @click="setTheme('dark')"
                            class="px-4 py-2 rounded-lg border text-sm font-medium transition"
                            :class="selectedTheme === 'dark'
                                ? 'bg-indigo-600 border-indigo-600 text-white'
                                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-gray-600'"
                        >🌙 {{ t('theme_dark') }}</button>
                    </div>
                </div>
            </div>

            <!-- Sécurité -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-8 h-8 rounded-lg bg-pink-100 dark:bg-pink-900/40 flex items-center justify-center text-sm flex-shrink-0">🔒</div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ t('security_title') }}</h3>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <form @submit.prevent="updatePassword" class="space-y-3">
                        <input v-model="passwordForm.current_password" type="password"
                               :placeholder="t('current_password')"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <input v-model="passwordForm.password" type="password"
                               :placeholder="t('new_password')"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <input v-model="passwordForm.password_confirmation" type="password"
                               :placeholder="t('confirm_new_password')"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
                        <p v-if="securityMessage" class="text-sm" :class="securityMessageType === 'error' ? 'text-red-500' : 'text-green-600'">
                            {{ securityMessage }}
                        </p>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition disabled:opacity-60"
                                :disabled="passwordLoading">
                            {{ t('update_password') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Compte -->
            <div class="rounded-xl border border-red-200 dark:border-red-900/50 overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-900/50">
                    <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/40 flex items-center justify-center text-sm flex-shrink-0">⚠️</div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ t('account_title') }}</h3>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 space-y-3">
                    <div class="flex flex-wrap gap-3">
                        <button @click="logout"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition">
                            🚪 {{ t('logout') }}
                        </button>
                        <button @click="showDeleteConfirmation = !showDeleteConfirmation"
                                class="px-4 py-2 border border-red-400 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                            🗑️ {{ t('delete_account') }}
                        </button>
                    </div>

                    <div v-if="showDeleteConfirmation"
                         class="p-4 rounded-xl border border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/20 space-y-3">
                        <p class="text-sm text-red-700 dark:text-red-300">{{ t('delete_warning') }}</p>
                        <input v-model="deletePassword" type="password" :placeholder="t('password')"
                               class="w-full rounded-lg border border-red-300 dark:border-red-700 dark:bg-gray-700 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 transition" />
                        <p v-if="deleteMessage" class="text-sm" :class="deleteMessageType === 'error' ? 'text-red-600' : 'text-green-600'">
                            {{ deleteMessage }}
                        </p>
                        <div class="flex gap-2 flex-wrap">
                            <button @click="deleteAccount"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition disabled:opacity-60"
                                    :disabled="deleteLoading">
                                {{ t('confirm_delete') }}
                            </button>
                            <button @click="showDeleteConfirmation = false"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                {{ t('cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

        </main>
    </div>
    <ChatIcon />
    <CommentWall />
</template>

<script setup>
import CommentWall from '@/Components/CommentWall.vue'
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useMoyennesStore } from '@/stores/moyennes'
import ChatIcon from '@/Components/ChatIcon.vue'
import axios from 'axios'


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

const translations = {
    fr: {
        brand_title: 'Mon Parcours',
        tab_filieres: 'Filières',
        tab_favorites: 'Filières Fav',
        tab_averages: 'Mes Moyennes',
        tab_settings: 'Paramètres',
        external_questionnaires: 'Mes Questionnaires',
        external_calcul: 'Calculer une moyenne',
        external_private_universities: 'Universités privées',
        logout: 'Déconnexion',
        criteria_label: 'Critères (mots-clés séparés par virgule)',
        criteria_placeholder: 'logique, maths, programmation...',
        criteria_help: 'Ces mots-clés servent à calculer la compatibilité avec le profil des étudiants.',
        search_placeholder: 'Rechercher (nom, code, université, type bac, formule...)',
        available_filieres: 'Filières disponibles',
        empty_filieres: 'Aucune filière',
        th_filiere: 'Filière (Licence)',
        th_code: 'Code',
        th_etablissement: 'Établissement',
        th_parcours: 'Parcours (Spécialité)',
        th_formule: 'Formule du score (T)',
        th_specialite: 'Spécialité',
        th_universite: 'Université',
        th_type_bac: 'Type de Baccalauréat',
        th_capacite: 'Capacité d\'accueil',
        th_score_dernier: 'Score dernier orienté',
        th_action: 'Action',
        favorites_title: 'Mes filières favorites',
        empty_favorites: 'Aucun favori',
        averages_title: 'Mes Moyennes',
        empty_averages: 'Aucune moyenne enregistrée',
        average_label: 'Moyenne',
        score_x2: 'Score ×2',
        score_plus_7: 'Score +7%',
        coef: 'Coef',
        close: 'Fermer',
        settings_title: 'Paramètres',
        language_title: 'Langue',
        theme_title: 'Thème',
        theme_light: 'Clair',
        theme_dark: 'Sombre',
        security_title: 'Sécurité',
        current_password: 'Mot de passe actuel',
        new_password: 'Nouveau mot de passe',
        confirm_new_password: 'Confirmer le nouveau mot de passe',
        update_password: 'Modifier le mot de passe',
        account_title: 'Compte',
        delete_account: 'Suppression du compte',
        delete_warning: 'Cette action est irreversible. Entrez votre mot de passe pour confirmer.',
        password: 'Mot de passe',
        confirm_delete: 'Confirmer la suppression',
        cancel: 'Annuler',
        no_category: 'Filières générales',
        no_licence: 'Licence non renseignée',
        password_required: 'Le mot de passe est obligatoire.',
        password_updated: 'Mot de passe modifie avec succes.',
        password_update_error: 'Erreur lors de la modification du mot de passe.',
        account_deleted: 'Compte supprime avec succes.',
        account_delete_error: 'Impossible de supprimer le compte.',
    },
    en: {
        brand_title: 'My Path',
        tab_filieres: 'Programs',
        tab_favorites: 'Favorites',
        tab_averages: 'My Grades',
        tab_settings: 'Settings',
        external_questionnaires: 'My Questionnaires',
        external_calcul: 'Calculate an average',
        external_private_universities: 'Private universities',
        logout: 'Logout',
        criteria_label: 'Criteria (comma-separated keywords)',
        criteria_placeholder: 'logic, math, programming...',
        criteria_help: 'These keywords are used to calculate compatibility with student profiles.',
        search_placeholder: 'Search (name, code, university, bac type, formula...)',
        available_filieres: 'Available programs',
        empty_filieres: 'No program found',
        th_filiere: 'Program (License)',
        th_code: 'Code',
        th_etablissement: 'Institution',
        th_parcours: 'Track (Specialty)',
        th_formule: 'Score formula (T)',
        th_specialite: 'Specialty',
        th_universite: 'University',
        th_type_bac: 'Baccalaureate Type',
        th_capacite: 'Capacity',
        th_score_dernier: 'Last admitted score',
        th_action: 'Action',
        favorites_title: 'My favorite programs',
        empty_favorites: 'No favorites',
        averages_title: 'My averages',
        empty_averages: 'No saved average',
        average_label: 'Average',
        score_x2: 'Score ×2',
        score_plus_7: 'Score +7%',
        coef: 'Coef',
        close: 'Close',
        settings_title: 'Settings',
        language_title: 'Language',
        theme_title: 'Theme',
        theme_light: 'Light',
        theme_dark: 'Dark',
        security_title: 'Security',
        current_password: 'Current password',
        new_password: 'New password',
        confirm_new_password: 'Confirm new password',
        update_password: 'Update password',
        account_title: 'Account',
        delete_account: 'Delete account',
        delete_warning: 'This action is irreversible. Enter your password to confirm.',
        password: 'Password',
        confirm_delete: 'Confirm deletion',
        cancel: 'Cancel',
        no_category: 'General programs',
        no_licence: 'License not specified',
        password_required: 'Password is required.',
        password_updated: 'Password updated successfully.',
        password_update_error: 'Error while updating password.',
        account_deleted: 'Account deleted successfully.',
        account_delete_error: 'Unable to delete account.',
    },
    ar: {
        brand_title: 'مساري',
        tab_filieres: 'التخصصات',
        tab_favorites: 'المفضلة',
        tab_averages: 'معدلاتي',
        tab_settings: 'الإعدادات',
        external_questionnaires: 'استبياناتي',
        external_calcul: 'حساب المعدل',
        external_private_universities: 'الجامعات الخاصة',
        logout: 'تسجيل الخروج',
        criteria_label: 'المعايير (كلمات مفتاحية مفصولة بفاصلة)',
        criteria_placeholder: 'منطق، رياضيات، برمجة...',
        criteria_help: 'تُستخدم هذه الكلمات لحساب مدى التوافق مع ملف الطالب.',
        search_placeholder: 'بحث (الاسم، الرمز، الجامعة، نوع الباك، الصيغة...)',
        available_filieres: 'التخصصات المتاحة',
        empty_filieres: 'لا توجد تخصصات',
        th_filiere: 'الإجازة / الشعبة',
        th_code: 'الرمز',
        th_etablissement: 'المؤسسة والجامعة',
        th_parcours: 'التخصصات',
        th_formule: 'صيغة احتساب مجموع النقاط',
        th_specialite: 'التخصص',
        th_universite: 'الجامعة',
        th_type_bac: 'نوع الباكالوريا',
        th_capacite: 'طاقة الاستيعاب',
        th_score_dernier: 'مجموع آخر موجه 2024',
        th_action: 'إجراء',
        favorites_title: 'تخصصاتي المفضلة',
        empty_favorites: 'لا توجد مفضلات',
        averages_title: 'معدلاتي',
        empty_averages: 'لا توجد معدلات محفوظة',
        average_label: 'المعدل',
        score_x2: 'النقطة ×2',
        score_plus_7: 'النقطة +7%',
        coef: 'المعامل',
        close: 'إغلاق',
        settings_title: 'الإعدادات',
        language_title: 'اللغة',
        theme_title: 'المظهر',
        theme_light: 'فاتح',
        theme_dark: 'داكن',
        security_title: 'الأمان',
        current_password: 'كلمة المرور الحالية',
        new_password: 'كلمة المرور الجديدة',
        confirm_new_password: 'تأكيد كلمة المرور الجديدة',
        update_password: 'تعديل كلمة المرور',
        account_title: 'الحساب',
        delete_account: 'حذف الحساب',
        delete_warning: 'هذا الإجراء لا يمكن التراجع عنه. أدخل كلمة المرور للتأكيد.',
        password: 'كلمة المرور',
        confirm_delete: 'تأكيد الحذف',
        cancel: 'إلغاء',
        no_category: 'تخصصات عامة',
        no_licence: 'الإجازة غير محددة',
        password_required: 'كلمة المرور مطلوبة.',
        password_updated: 'تم تحديث كلمة المرور بنجاح.',
        password_update_error: 'حدث خطأ أثناء تحديث كلمة المرور.',
        account_deleted: 'تم حذف الحساب بنجاح.',
        account_delete_error: 'تعذر حذف الحساب.',
    },
}

const selectedLanguage = ref('fr')
const selectedTheme = ref('light')
const passwordLoading = ref(false)
const deleteLoading = ref(false)
const securityMessage = ref('')
const securityMessageType = ref('success')
const deleteMessage = ref('')
const deleteMessageType = ref('success')
const showDeleteConfirmation = ref(false)
const deletePassword = ref('')
const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const currentLocale = computed(() => translations[selectedLanguage.value] || translations.fr)
const t = (key) => currentLocale.value[key] || translations.fr[key] || key

const tabs = computed(() => ([
    { id: 'filieres', icon: '🎓', label: t('tab_filieres') },
    { id: 'favoris', icon: '❤️', label: t('tab_favorites') },
    { id: 'mesMoyennes', icon: '📊', label: t('tab_averages') },
    { id: 'parametres', icon: '⚙️', label: t('tab_settings') },
]))

const languageOptions = [
    { value: 'fr', label: 'Français' },
    { value: 'ar', label: 'Arabe' },
    { value: 'en', label: 'Anglais' },
]

// ── Store moyennes ─────────────────────────────────────────────────────────────
const moyennesStore = useMoyennesStore()
onMounted(() => {
    moyennesStore.fetchMoyennes()
    const saved = localStorage.getItem('favoriteFilieres')
    if (saved) {
        try {
            favoriteFilieres.value = JSON.parse(saved)
        } catch (e) {
            favoriteFilieres.value = []
        }
    }
    sanitizeFavorites()
    initSettings()
})

// ── Filières & Favoris ─────────────────────────────────────────────────────────
const search           = ref('')
const favoriteFilieres = ref([])

const ARABIC_RE = /[\u0600-\u06FF\uFB50-\uFDFF\uFE70-\uFEFF]/
const isArabicText = (text) => {
    if (!text) return false
    return ARABIC_RE.test(text)
}

const filteredFilieres = computed(() => {
    if (!search.value) return props.filieres
    const s = search.value.toLowerCase()
    return props.filieres.filter(f => {
        const searchable = [
            f.specialite,
            f.nom,
            f.licence,
            f.code,
            f.universite,
            f.type_bac,
            f.formule,
            f.capacite,
            f.score_dernier_oriente_2025,
            ...(Array.isArray(f.criteres) ? f.criteres : []),
        ]
            .filter(v => v !== null && v !== undefined)
            .map(v => String(v).toLowerCase())
            .join(' ')

        return searchable.indexOf(s) !== -1
    })
})

const favFilieresData = computed(() =>
    props.filieres.filter(f => favoriteFilieres.value.includes(f.id))
)

const favoriteCount = computed(() => favFilieresData.value.length)

const groupedFilieres = computed(() => {
    const critMap = new Map()

    for (const f of filteredFilieres.value) {
        const critLabel = Array.isArray(f.criteres) && f.criteres.length
            ? String(f.criteres[0])
            : t('no_category')

        if (!critMap.has(critLabel)) critMap.set(critLabel, new Map())
        const licMap = critMap.get(critLabel)

        const licLabel = (f.licence || f.specialite || f.nom || t('no_licence'))
        if (!licMap.has(licLabel)) licMap.set(licLabel, [])
        licMap.get(licLabel).push(f)
    }

    return Array.from(critMap.entries()).map(([label, licMap]) => ({
        label,
        licences: Array.from(licMap.entries()).map(([licence, rows]) => ({
            label: licence,
            rows,
        })),
    }))
})

const isFav = (id) => favoriteFilieres.value.includes(id)

const toggleFav = (id) => {
    const i = favoriteFilieres.value.indexOf(id)
    i === -1
        ? favoriteFilieres.value.push(id)
        : favoriteFilieres.value.splice(i, 1)
    sanitizeFavorites()
}

function sanitizeFavorites() {
    const validIds = new Set(props.filieres.map(f => f.id))
    favoriteFilieres.value = favoriteFilieres.value.filter(id => validIds.has(id))
    localStorage.setItem('favoriteFilieres', JSON.stringify(favoriteFilieres.value))
}

// ── Modale moyenne ─────────────────────────────────────────────────────────────
const showModal = ref(false)
const selectedM = ref(null)
const openModal = (m) => { selectedM.value = m; showModal.value = true }

// ── Helpers ────────────────────────────────────────────────────────────────────
const getIntlLocale = () => {
    if (selectedLanguage.value === 'en') return 'en-US'
    if (selectedLanguage.value === 'ar') return 'ar'
    return 'fr-FR'
}

const fmtDate = (d) =>
    new Date(d).toLocaleDateString(getIntlLocale(), {
        day: '2-digit', month: '2-digit', year: 'numeric'
    })

// ── Navigation externe ─────────────────────────────────────────────────────────
const goToQuestionnaires = () => router.visit(route('etudiant.questionnaires.index'))
const goToCalcul         = () => router.visit(route('etudiant.calcul-moyennes'))
const logout             = () => router.post('/logout')

const initSettings = async () => {
    const localLanguage = localStorage.getItem('language')
    if (localLanguage && languageOptions.some(option => option.value === localLanguage)) {
        applyLanguage(localLanguage)
    }

    const theme = localStorage.getItem('theme')
    if (theme === 'dark') {
        setTheme('dark')
    } else {
        setTheme('light')
    }

    try {
        const { data } = await axios.get(route('language.current'))
        if (languageOptions.some(option => option.value === data.language)) {
            applyLanguage(data.language)
        }
    } catch {
        applyLanguage('fr')
    }
}

const changeLanguage = async (language) => {
    const previousLanguage = selectedLanguage.value
    applyLanguage(language)
    try {
        await axios.put(route('language.update'), { language })
    } catch {
        applyLanguage(previousLanguage)
    }
}

const applyLanguage = (language) => {
    selectedLanguage.value = language
    localStorage.setItem('language', language)
    document.documentElement.setAttribute('lang', language)
    document.documentElement.setAttribute('dir', language === 'ar' ? 'rtl' : 'ltr')
}

const setTheme = (theme) => {
    selectedTheme.value = theme
    if (theme === 'dark') {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
    localStorage.setItem('theme', theme)
}

const updatePassword = async () => {
    securityMessage.value = ''
    passwordLoading.value = true
    try {
        const { data } = await axios.put(route('password.update'), passwordForm.value)
        securityMessageType.value = 'success'
        securityMessage.value = data.message || t('password_updated')
        passwordForm.value = {
            current_password: '',
            password: '',
            password_confirmation: '',
        }
    } catch (error) {
        securityMessageType.value = 'error'
        securityMessage.value = error?.response?.data?.message || t('password_update_error')
    } finally {
        passwordLoading.value = false
    }
}

const deleteAccount = async () => {
    deleteMessage.value = ''

    if (!deletePassword.value) {
        deleteMessageType.value = 'error'
        deleteMessage.value = t('password_required')
        return
    }

    deleteLoading.value = true
    try {
        const { data } = await axios.delete(route('account.destroy'), {
            data: { password: deletePassword.value },
        })
        deleteMessageType.value = 'success'
        deleteMessage.value = data.message || t('account_deleted')
        setTimeout(() => {
            router.visit(route('login'))
        }, 700)
    } catch (error) {
        deleteMessageType.value = 'error'
        deleteMessage.value = error?.response?.data?.message || t('account_delete_error')
    } finally {
        deleteLoading.value = false
    }
}

// ⚠️ IL NE DOIT PAS Y AVOIR DE CODE ICI APRÈS LES FONCTIONS ⚠️
// Supprimez tout ce qui ressemble à :
// if (!reponses.length) return
// qSendForm.reponses = reponses
</script>

<style scoped>
* { transition: background-color 0.2s ease, border-color 0.2s ease; }
</style>