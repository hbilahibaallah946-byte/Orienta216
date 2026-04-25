<template>
    <div class="min-h-screen flex bg-[#F4F7FE] dark:bg-[#0B1437]" :class="{ 'font-ar': selectedLanguage === 'ar' }">

        <!-- ── Sidebar ──────────────────────────────────────────────── -->
        <div :class="[
                'fixed inset-y-0 left-0 w-[240px] p-5 transform transition-transform duration-300 z-40 flex flex-col',
                'bg-[#1E3A8A] dark:bg-[#0F172A]',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'md:translate-x-0 md:relative md:z-0'
             ]">
            <div class="flex items-center gap-3 mb-8 px-2">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#60A5FA] to-[#2563EB] flex items-center justify-center text-white font-bold text-base shadow-lg shadow-blue-900/40">🎓</div>
                <h2 class="text-white font-bold text-[17px] tracking-tight">{{ t('brand_title') }}</h2>
            </div>
            <nav class="flex-1 space-y-1">
                <button v-for="tab in tabs" :key="tab.id"
                        @click="activeTab = tab.id"
                        class="w-full text-left py-2.5 px-3 rounded-xl transition-all duration-200 flex items-center gap-3 text-sm font-medium relative group"
                        :class="activeTab === tab.id
                            ? 'bg-white/10 text-white'
                            : 'text-[#8F9BBA] hover:bg-white/5 hover:text-white'">
                    <span class="w-5 text-base leading-none flex items-center justify-center">{{ tab.icon }}</span>
                    <span>{{ tab.label }}</span>
                    <span v-if="activeTab === tab.id" class="absolute right-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-[#60A5FA] rounded-l-full"></span>
                    <span v-if="tab.id === 'favoris' && favoriteCount"
                          class="ml-auto bg-[#FF5A5A] text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">
                        {{ favoriteCount }}
                    </span>
                </button>

                <div class="border-t border-white/10 my-3"></div>

                <button @click="goToPrivateUniversities"
                        class="w-full text-left py-2.5 px-3 rounded-xl text-[#8F9BBA] hover:bg-white/5 hover:text-white transition-all duration-200 text-sm flex items-center gap-3">
                    <span class="w-5 text-base leading-none flex items-center justify-center">🏫</span>
                    <span>{{ t('external_private_universities') }}</span>
                </button>
            </nav>

            <button @click="logout"
                    class="mt-4 w-full flex items-center gap-3 py-2.5 px-3 rounded-xl text-[#FF5A5A] hover:bg-white/5 transition-all duration-200 text-sm font-medium border border-[#FF5A5A]/20">
                <span>{{ t('logout') }}</span>
            </button>
        </div>

        <!-- Mobile toggle -->
        <button @click="sidebarOpen = !sidebarOpen"
                class="fixed top-4 left-4 md:hidden z-50 bg-[#1E3A8A] p-3 rounded-xl text-white shadow-lg"
                :class="{ 'left-[240px]': sidebarOpen }">
            <span v-if="!sidebarOpen">☰</span><span v-else>✕</span>
        </button>

        <!-- ── Main content ──────────────────────────────────────────── -->
        <main class="flex-1 overflow-auto min-h-screen">

            <!-- Top bar -->
            <div class="sticky top-0 z-30 bg-[#F4F7FE]/80 dark:bg-[#0B1437]/80 backdrop-blur-md border-b border-white/60 dark:border-white/5 px-6 py-3 flex items-center gap-3">
                <div v-if="activeTab === 'filieres' || activeTab === 'favoris'" class="relative flex-1 max-w-sm">
                    <input v-model="search" type="text"
                           :placeholder="t('search_placeholder')"
                           class="w-full pl-9 pr-4 py-2 rounded-xl bg-white dark:bg-[#1E3A8A] border border-gray-100 dark:border-white/10 text-sm text-gray-700 dark:text-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#60A5FA]/30 placeholder-gray-400"/>
                </div>
                <div v-else class="flex-1"></div>
                <div class="ml-auto flex items-center gap-2">
                </div>
            </div>

            <div class="p-6">

            <!-- ── FILIÈRES ─────────────────────────────────────────── -->
            <div v-if="activeTab === 'filieres'" class="space-y-5">
                <div class="bg-white dark:bg-[#1E3A8A] rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                        <h2 class="text-base font-bold text-[#1E3A8A] dark:text-white">{{ t('available_filieres') }}</h2>
                        <span class="text-xs font-semibold text-[#60A5FA] bg-[#F4F7FE] dark:bg-[#0B1437] px-3 py-1 rounded-full">
                            {{ filteredFilieres.length }} résultats
                        </span>
                    </div>
                    <div v-if="!props.filieres?.length" class="text-[#A3AED0] text-center py-16 text-sm">{{ t('empty_filieres') }}</div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#F4F7FE] dark:bg-[#0B1437]">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_filiere') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_code') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_etablissement') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_parcours') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_type_bac') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_formule') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_capacite') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_score_dernier') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">❤️</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(critGroup, cIdx) in groupedFilieres" :key="`crit-${cIdx}-${critGroup.label}`">
                                    <tr class="bg-gradient-to-r from-[#60A5FA]/8 to-transparent dark:from-[#60A5FA]/10">
                                        <td colspan="9" class="px-4 py-2.5 font-semibold text-[#2563EB] dark:text-[#60A5FA] text-xs uppercase tracking-wider"
                                            :dir="isArabicText(critGroup.label) ? 'rtl' : 'ltr'">
                                            {{ critGroup.label }}
                                        </td>
                                    </tr>
                                    <template v-for="(licGroup, lIdx) in critGroup.licences" :key="`lic-${cIdx}-${lIdx}-${licGroup.label}`">
                                        <tr v-for="(f, rIdx) in licGroup.rows" :key="`f-${f.id}`"
                                            class="border-t border-gray-50 dark:border-white/5 hover:bg-[#F4F7FE] dark:hover:bg-white/5 transition-colors duration-150">
                                            <td v-if="rIdx === 0" :rowspan="licGroup.rows.length"
                                                class="px-4 py-3 align-top font-semibold text-[#1E3A8A] dark:text-white text-sm"
                                                :dir="isArabicText(licGroup.label) ? 'rtl' : 'ltr'">
                                                {{ licGroup.label }}
                                            </td>
                                            <td class="px-4 py-3 text-[#A3AED0] font-mono text-xs">{{ f.code || '-' }}</td>
                                            <td class="px-4 py-3 text-[#1E3A8A] dark:text-gray-200" :dir="isArabicText(f.universite) ? 'rtl' : 'ltr'">{{ f.universite || '-' }}</td>
                                            <td class="px-4 py-3 text-[#1E3A8A] dark:text-gray-200" :dir="isArabicText(f.specialite || f.nom) ? 'rtl' : 'ltr'">{{ f.specialite || f.nom || '-' }}</td>
                                            <td class="px-4 py-3">
                                                <span v-if="f.type_bac" class="inline-flex items-center px-2 py-0.5 rounded-lg bg-[#EFF4FB] dark:bg-white/10 text-[#1E3A8A] dark:text-gray-200 text-xs font-medium" :dir="isArabicText(f.type_bac) ? 'rtl' : 'ltr'">{{ f.type_bac }}</span>
                                                <span v-else class="text-[#A3AED0]">-</span>
                                            </td>
                                            <td class="px-4 py-3 text-[#1E3A8A] dark:text-gray-200 text-xs font-mono">{{ f.formule || '-' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span v-if="f.capacite" class="text-[#1E3A8A] dark:text-gray-200 font-semibold">{{ f.capacite }}</span>
                                                <span v-else class="text-[#A3AED0]">-</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span v-if="f.score_dernier_oriente_2025" class="font-bold text-[#05CD99]">{{ f.score_dernier_oriente_2025 }}</span>
                                                <span v-else class="text-[#A3AED0]">-</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button @click="toggleFav(f.id)"
                                                        class="transition-all duration-200 hover:scale-125 focus:outline-none"
                                                        :class="isFav(f.id) ? 'text-[#FF5A5A]' : 'text-gray-200 dark:text-gray-600 hover:text-[#FF5A5A]'">❤️</button>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── FAVORIS ──────────────────────────────────────────── -->
            <div v-if="activeTab === 'favoris'" class="space-y-5">
                <div>
                    <h1 class="text-2xl font-bold text-[#1E3A8A] dark:text-white">{{ t('favorites_title') }} ❤️</h1>
                    <p class="text-[#A3AED0] text-sm mt-0.5">{{ favoriteCount }} filière(s) enregistrée(s)</p>
                </div>
                <div class="bg-white dark:bg-[#1E3A8A] rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm overflow-hidden">
                    <div v-if="!favFilieresData.length" class="text-[#A3AED0] text-center py-16 text-sm">{{ t('empty_favorites') }}</div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#F4F7FE] dark:bg-[#0B1437]">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_specialite') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_universite') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_type_bac') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_score_dernier') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-[#A3AED0] uppercase tracking-wider">{{ t('th_action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="f in favFilieresData" :key="f.id"
                                    class="border-t border-gray-50 dark:border-white/5 hover:bg-[#F4F7FE] dark:hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-[#1E3A8A] dark:text-white">{{ f.specialite || f.nom }}</td>
                                    <td class="px-4 py-3 text-[#1E3A8A] dark:text-gray-200">{{ f.universite || '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-[#EFF4FB] dark:bg-white/10 text-[#1E3A8A] dark:text-gray-200 text-xs font-medium">{{ f.type_bac || '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-[#05CD99]">{{ f.score_dernier_oriente_2025 || '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button @click="toggleFav(f.id)" class="text-xl text-[#FF5A5A] hover:scale-125 transition-transform duration-200">💔</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── QUESTIONNAIRES ────────────────────────────────────── -->
            <div v-if="activeTab === 'questionnaires'" class="space-y-5">
                <div>
                    <h1 class="text-2xl font-bold text-[#1E3A8A] dark:text-white"> {{ t('my_questionnaires') }}</h1>
                    <p class="text-[#A3AED0] text-sm mt-0.5">{{ questionnaires.length }} questionnaire(s)</p>
                </div>

                <div v-if="$page.props.flash?.success"
                     class="p-4 bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-xl flex items-center gap-2">
                    <span class="text-green-700 dark:text-green-300 font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <div v-if="Object.keys(questionnaireForm.errors).length"
                     class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-xl">
                    <p class="text-red-700 dark:text-red-300 font-medium mb-1"> {{ t('questionnaire_errors') }}</p>
                    <ul class="text-sm text-red-600 list-disc list-inside">
                        <li v-for="(e, k) in questionnaireForm.errors" :key="k">{{ e }}</li>
                    </ul>
                </div>

                <div v-if="questionnaires.length" class="space-y-4">
                    <div v-for="q in questionnaires" :key="q.id"
                        class="bg-white dark:bg-[#1E3A8A] rounded-xl shadow-sm border border-gray-100 dark:border-white/10 p-5 space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-[#1E3A8A] dark:text-white">{{ q.titre }}</p>
                                <p class="text-sm text-[#A3AED0] mt-1">
                                    {{ t('questionnaire_from') }} {{ q.conseiller?.name || '-' }} •
                                    {{ q.questions?.length || 0 }} {{ t('questionnaire_questions_count') }} •
                                    {{ t('questionnaire_received_on') }} {{ formatDate(q.envoye_le) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span :class="q.statut === 'repondu'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                    : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300'"
                                    class="text-xs font-semibold px-3 py-1 rounded-full">
                                    {{ q.statut === 'repondu' ? t('questionnaire_done') : t('questionnaire_pending') }}
                                </span>
                                <button
                                    @click="toggleQuestionnaire(q)"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded-lg transition"
                                >
                                    {{ openedQuestionnaireId === q.id ? t('questionnaire_close') : t('questionnaire_answer') }}
                                </button>
                            </div>
                        </div>

                        <div v-if="openedQuestionnaireId === q.id" class="space-y-4 border-t border-gray-100 dark:border-white/10 pt-4">
                            <div v-if="q.statut === 'repondu'"
                                 class="p-3 bg-green-50 dark:bg-green-900/30 border border-green-300 rounded-xl text-center text-green-700 dark:text-green-300 text-sm font-medium">
                                {{ t('questionnaire_already_answered') }}
                            </div>

                            <div v-for="(question, idx) in q.questions || []" :key="question.id"
                                 class="bg-[#F4F7FE] dark:bg-[#0B1437] rounded-xl border border-gray-100 dark:border-white/10 shadow-sm p-5">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-sm font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                                        {{ idx + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <span v-if="question.categorie"
                                              class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-2"
                                              :class="question.categorie === 'competence'
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                                    : question.categorie === 'preference'
                                                        ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'
                                                        : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'">
                                            {{ categoryLabel(question.categorie) }}
                                        </span>
                                        <p class="font-semibold text-[#1E3A8A] dark:text-white mb-3">{{ question.texte }}</p>

                                        <textarea v-if="question.type === 'text'"
                                                  v-model="answers[question.id]" rows="3"
                                                  class="w-full border border-gray-200 dark:border-gray-600 rounded-lg p-3 text-sm bg-white dark:bg-[#1E3A8A] dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 outline-none resize-none"
                                                  :placeholder="t('questionnaire_placeholder')"/>

                                        <div v-else-if="question.type === 'choix_unique'" class="space-y-2">
                                            <label v-for="opt in question.options || []" :key="opt" class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" :name="`q_${question.id}`" :value="opt" v-model="answers[question.id]" class="text-indigo-600"/>
                                                <span class="text-sm dark:text-gray-200">{{ opt }}</span>
                                            </label>
                                        </div>

                                        <div v-else-if="question.type === 'choix_multiple'" class="space-y-2">
                                            <label v-for="opt in question.options || []" :key="opt" class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" :value="opt"
                                                       :checked="(multipleAnswers[question.id] ?? []).includes(opt)"
                                                       @change="toggleMultiple(question.id, opt)"
                                                       class="text-indigo-600"/>
                                                <span class="text-sm dark:text-gray-200">{{ opt }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-[#F4F7FE] dark:bg-[#0B1437] rounded-xl p-4 border border-gray-100 dark:border-white/10">
                                <div class="flex justify-between text-sm text-[#A3AED0] mb-2">
                                    <span>{{ t('questionnaire_answered') }}</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ answeredCount(q) }} / {{ q.questions?.length || 0 }}</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500"
                                         :style="{ width: progressPercent(q) + '%' }"></div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button @click="submitQuestionnaire(q)"
                                        :disabled="questionnaireForm.processing || answeredCount(q) === 0"
                                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition flex items-center gap-2">
                                    <span v-if="questionnaireForm.processing">{{ t('questionnaire_submitting') }}</span>
                                    <span v-else>{{ t('questionnaire_submit') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 text-[#A3AED0]">
                    <div class="text-5xl mb-3"></div>
                    <p>{{ t('empty_questionnaires') }}</p>
                </div>
            </div>

            <!-- ── MES MOYENNES ─────────────────────────────────────── -->
            <div v-if="activeTab === 'mesMoyennes'" class="space-y-5">
                <div>
                    <h1 class="text-2xl font-bold text-[#1E3A8A] dark:text-white">{{ t('averages_title') }}</h1>
                    <p class="text-[#A3AED0] text-sm mt-0.5">{{ moyennesStore.moyennes.length }} entrée(s)</p>
                </div>
                <div v-if="moyennesStore.loading" class="flex items-center justify-center py-24">
                    <div class="w-10 h-10 rounded-full border-[3px] border-[#60A5FA] border-t-transparent animate-spin"></div>
                </div>
                <div v-else-if="!moyennesStore.moyennes.length" class="bg-white dark:bg-[#1E3A8A] rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm p-16 text-center text-[#A3AED0] text-sm">
                    {{ t('empty_averages') }}
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="m in moyennesStore.moyennes" :key="m.id"
                         @click="openModal(m)"
                         class="bg-white dark:bg-[#1E3A8A] rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm overflow-hidden cursor-pointer hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                        <div class="bg-gradient-to-r from-[#2563EB] to-[#60A5FA] p-5 relative overflow-hidden">
                            <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-white/10"></div>
                            <div class="absolute -bottom-6 -right-2 w-16 h-16 rounded-full bg-white/5"></div>
                            <h3 class="font-bold text-white text-sm relative z-10">{{ m.specialite }}</h3>
                            <p class="text-blue-200 text-xs mt-0.5 relative z-10">{{ fmtDate(m.created_at) }}</p>
                        </div>
                        <div class="p-4">
                            <div class="flex justify-between items-baseline mb-2">
                                <span class="text-xs text-[#A3AED0]">{{ t('average_label') }}</span>
                                <span class="text-2xl font-bold text-[#1E3A8A] dark:text-white">{{ m.moyenne }}<span class="text-sm font-normal text-[#A3AED0]">/20</span></span>
                            </div>
                            <div class="w-full bg-[#EFF4FB] dark:bg-white/10 rounded-full h-1.5 mb-3">
                                <div class="bg-gradient-to-r from-[#2563EB] to-[#60A5FA] h-1.5 rounded-full" :style="{ width: (m.moyenne / 20 * 100) + '%' }"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-gray-50 dark:border-white/5 text-xs">
                                <div class="bg-[#F4F7FE] dark:bg-white/5 rounded-xl p-2.5">
                                    <p class="text-[#A3AED0] mb-0.5">{{ t('score_x2') }}</p>
                                    <p class="font-bold text-[#05CD99]">{{ m.score }}</p>
                                </div>
                                <div class="bg-[#F4F7FE] dark:bg-white/5 rounded-xl p-2.5">
                                    <p class="text-[#A3AED0] mb-0.5">{{ t('score_plus_7') }}</p>
                                    <p class="font-bold text-[#FFCE20]">{{ m.score_plus_7 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── CALCUL DE MOYENNE ───────────────────────────────── -->
            <div v-if="activeTab === 'calcul'" class="space-y-5">
                <CalculMoyennes
                    :language="selectedLanguage"
                    :theme="selectedTheme"
                    :t="t"
                    @done="activeTab = 'mesMoyennes'"
                />
            </div>

            <!-- ── PARAMÈTRES ───────────────────────────────────────── -->
            <div v-if="activeTab === 'parametres'" class="max-w-2xl space-y-5">
                <div>
                    <h1 class="text-2xl font-bold text-[#1E3A8A] dark:text-white">{{ t('settings_title') }}</h1>
                    <p class="text-[#A3AED0] text-sm mt-0.5">{{ t('settings_subtitle') }}</p>
                </div>
                <!-- Langue -->
                <div class="bg-white dark:bg-[#1E3A8A] rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-50 dark:border-white/5">
                        <div class="w-9 h-9 rounded-xl bg-[#E6F1FB] dark:bg-[#185FA5]/20 flex items-center justify-center flex-shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#185FA5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1E3A8A] dark:text-white">{{ t('language_title') }}</h3>
                            <p class="text-xs text-[#A3AED0] mt-0.5">Choisissez la langue de l'interface</p>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="flex flex-wrap gap-2">
                            <button v-for="lang in languageOptions" :key="lang.value"
                                    @click="changeLanguage(lang.value)"
                                    class="px-4 py-2 rounded-xl border text-sm font-medium transition-all duration-200"
                                    :class="selectedLanguage === lang.value
                                        ? 'bg-[#0C447C] border-[#0C447C] text-white shadow-md shadow-blue-900/20'
                                        : 'bg-[#F4F7FE] dark:bg-white/5 border-gray-100 dark:border-white/10 text-[#1E3A8A] dark:text-gray-200 hover:border-[#378ADD] hover:text-[#185FA5]'"
                            >{{ lang.label }}</button>
                        </div>
                    </div>
                </div>
                <!-- Thème -->
                <div class="bg-white dark:bg-[#1E3A8A] rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-50 dark:border-white/5">
                        <div class="w-9 h-9 rounded-xl bg-[#EEEDFE] dark:bg-[#534AB7]/20 flex items-center justify-center flex-shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#534AB7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"/>
                                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1E3A8A] dark:text-white">{{ t('theme_title') }}</h3>
                            <p class="text-xs text-[#A3AED0] mt-0.5">Mode clair ou sombre selon vos préférences</p>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="flex flex-wrap gap-2">
                            <button @click="setTheme('light')"
                                    class="flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-medium transition-all duration-200"
                                    :class="selectedTheme === 'light'
                                        ? 'bg-[#0C447C] border-[#0C447C] text-white shadow-md shadow-blue-900/20'
                                        : 'bg-[#F4F7FE] dark:bg-white/5 border-gray-100 dark:border-white/10 text-[#1E3A8A] dark:text-gray-200 hover:border-[#378ADD]'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="4"/>
                                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                                </svg>
                                {{ t('theme_light') }}
                            </button>
                            <button @click="setTheme('dark')"
                                    class="flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-medium transition-all duration-200"
                                    :class="selectedTheme === 'dark'
                                        ? 'bg-[#0C447C] border-[#0C447C] text-white shadow-md shadow-blue-900/20'
                                        : 'bg-[#F4F7FE] dark:bg-white/5 border-gray-100 dark:border-white/10 text-[#1E3A8A] dark:text-gray-200 hover:border-[#378ADD]'">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                                </svg>
                                {{ t('theme_dark') }}
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Sécurité -->
                <div class="bg-white dark:bg-[#1E3A8A] rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-50 dark:border-white/5">
                        <div class="w-9 h-9 rounded-xl bg-[#FBEAF0] dark:bg-[#993556]/20 flex items-center justify-center flex-shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#993556" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-[#1E3A8A] dark:text-white">{{ t('security_title') }}</h3>
                            <p class="text-xs text-[#A3AED0] mt-0.5">Modification du mot de passe</p>
                        </div>
                    </div>
                    <div class="p-5 space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#A3AED0] uppercase tracking-wider mb-1.5">
                                {{ t('current_password') }}
                            </label>
                            <input v-model="passwordForm.current_password" type="password"
                                   :placeholder="t('current_password')"
                                   class="w-full rounded-xl border border-gray-100 dark:border-white/10 bg-[#F4F7FE] dark:bg-[#0B1437] dark:text-white text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#378ADD]/30 focus:border-[#378ADD] placeholder-gray-400 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#A3AED0] uppercase tracking-wider mb-1.5">
                                {{ t('new_password') }}
                            </label>
                            <input v-model="passwordForm.password" type="password"
                                   :placeholder="t('new_password')"
                                   class="w-full rounded-xl border border-gray-100 dark:border-white/10 bg-[#F4F7FE] dark:bg-[#0B1437] dark:text-white text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#378ADD]/30 focus:border-[#378ADD] placeholder-gray-400 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#A3AED0] uppercase tracking-wider mb-1.5">
                                {{ t('confirm_new_password') }}
                            </label>
                            <input v-model="passwordForm.password_confirmation" type="password"
                                   :placeholder="t('confirm_new_password')"
                                   class="w-full rounded-xl border border-gray-100 dark:border-white/10 bg-[#F4F7FE] dark:bg-[#0B1437] dark:text-white text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#378ADD]/30 focus:border-[#378ADD] placeholder-gray-400 transition" />
                        </div>
                        <p v-if="securityMessage" class="text-sm font-medium" :class="securityMessageType === 'error' ? 'text-[#A32D2D]' : 'text-[#05CD99]'">
                            {{ securityMessage }}
                        </p>
                        <button @click="updatePassword"
                                class="flex items-center gap-2 px-5 py-2.5 bg-[#0C447C] hover:bg-[#185FA5] text-white rounded-xl text-sm font-semibold transition shadow-sm disabled:opacity-60"
                                :disabled="passwordLoading">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            {{ t('update_password') }}
                        </button>
                    </div>
                </div>
                <!-- Compte / Danger zone -->
                <div class="bg-white dark:bg-[#1E3A8A] rounded-2xl border border-[#F09595]/60 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-[#F09595]/30 bg-[#FCEBEB]/60 dark:bg-[#A32D2D]/10">
                        <div class="w-9 h-9 rounded-xl bg-[#FCEBEB] dark:bg-[#A32D2D]/20 flex items-center justify-center flex-shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#A32D2D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-[#A32D2D]">{{ t('account_title') }}</h3>
                            <p class="text-xs text-[#A32D2D]/70 mt-0.5">Actions irréversibles — procéder avec précaution</p>
                        </div>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <button @click="logout"
                                    class="flex items-center gap-2 px-4 py-2.5 bg-[#F4F7FE] dark:bg-white/5 border border-gray-100 dark:border-white/10 text-[#1E3A8A] dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-gray-100 dark:hover:bg-white/10 transition">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                {{ t('logout') }}
                            </button>
                            <button @click="showDeleteConfirmation = !showDeleteConfirmation"
                                    class="flex items-center gap-2 px-4 py-2.5 border border-[#F09595] text-[#A32D2D] rounded-xl text-sm font-semibold hover:bg-[#FCEBEB] dark:hover:bg-[#A32D2D]/10 transition">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#A32D2D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                </svg>
                                {{ t('delete_account') }}
                            </button>
                        </div>
                        <div v-if="showDeleteConfirmation" class="p-4 rounded-xl border border-[#F09595]/60 bg-[#FCEBEB]/50 dark:bg-[#A32D2D]/10 space-y-3">
                            <div class="flex items-start gap-2.5">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#A32D2D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 mt-0.5">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                    <line x1="12" y1="9" x2="12" y2="13"/>
                                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                                <p class="text-sm text-[#A32D2D] leading-relaxed">{{ t('delete_warning') }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#A32D2D]/80 uppercase tracking-wider mb-1.5">
                                    {{ t('password') }}
                                </label>
                                <input v-model="deletePassword" type="password" :placeholder="t('password')"
                                       class="w-full rounded-xl border border-[#F09595] bg-white dark:bg-[#0B1437] dark:text-white text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#F09595]/30 placeholder-gray-400 transition" />
                            </div>
                            <p v-if="deleteMessage" class="text-sm font-medium" :class="deleteMessageType === 'error' ? 'text-[#A32D2D]' : 'text-[#05CD99]'">{{ deleteMessage }}</p>
                            <div class="flex gap-2 flex-wrap">
                                <button @click="deleteAccount"
                                        class="flex items-center gap-2 px-4 py-2.5 bg-[#A32D2D] hover:bg-[#791F1F] text-white rounded-xl text-sm font-semibold transition disabled:opacity-60"
                                        :disabled="deleteLoading">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                    {{ t('confirm_delete') }}
                                </button>
                                <button @click="showDeleteConfirmation = false"
                                        class="px-4 py-2.5 border border-gray-100 dark:border-white/10 rounded-xl text-sm text-[#1E3A8A] dark:text-gray-200 hover:bg-[#F4F7FE] dark:hover:bg-white/5 transition">
                                    {{ t('cancel') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>
        </main>

        <!-- ── Modal moyenne ─────────────────────────────────────────── -->
        <Teleport to="body">
            <div v-if="showModal"
                 class="fixed inset-0 bg-[#1E3A8A]/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                 @click.self="showModal = false">
                <div class="bg-white dark:bg-[#1E3A8A] rounded-2xl shadow-2xl max-w-lg w-full max-h-[85vh] overflow-y-auto border border-gray-100 dark:border-white/10">
                    <div class="sticky top-0 bg-gradient-to-r from-[#2563EB] to-[#60A5FA] p-5 rounded-t-2xl flex justify-between items-start">
                        <div>
                            <h2 class="text-lg font-bold text-white">{{ selectedM?.specialite }}</h2>
                            <p class="text-blue-200 text-xs mt-0.5">{{ selectedM ? fmtDate(selectedM.created_at) : '' }}</p>
                        </div>
                        <button @click="showModal = false" class="w-8 h-8 rounded-xl bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition text-lg leading-none">×</button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-[#F4F7FE] dark:bg-[#0B1437] p-3 rounded-xl text-center">
                                <p class="text-xs text-[#A3AED0] mb-1">{{ t('average_label') }}</p>
                                <p class="text-xl font-bold text-[#1E3A8A] dark:text-white">{{ selectedM?.moyenne }}<span class="text-xs text-[#A3AED0] font-normal">/20</span></p>
                            </div>
                            <div class="bg-[#F4F7FE] dark:bg-[#0B1437] p-3 rounded-xl text-center">
                                <p class="text-xs text-[#A3AED0] mb-1">{{ t('score_x2') }}</p>
                                <p class="text-xl font-bold text-[#05CD99]">{{ selectedM?.score }}</p>
                            </div>
                            <div class="bg-[#F4F7FE] dark:bg-[#0B1437] p-3 rounded-xl text-center">
                                <p class="text-xs text-[#A3AED0] mb-1">{{ t('score_plus_7') }}</p>
                                <p class="text-xl font-bold text-[#FFCE20]">{{ selectedM?.score_plus_7 }}</p>
                            </div>
                        </div>
                        <div v-for="(mat, i) in selectedM?.matieres" :key="i"
                             class="bg-[#F4F7FE] dark:bg-[#0B1437] rounded-xl p-3.5">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-semibold text-sm text-[#1E3A8A] dark:text-white">{{ mat.nom }}</span>
                                    <span class="text-xs text-[#A3AED0] ml-2">{{ t('coef') }} {{ mat.coefficient }}</span>
                                </div>
                                <span class="font-bold text-base px-2 py-0.5 rounded-lg"
                                      :class="parseFloat(mat.note) >= 12 ? 'text-[#05CD99] bg-[#05CD99]/10' : parseFloat(mat.note) >= 10 ? 'text-[#FFCE20] bg-[#FFCE20]/10' : 'text-[#FF5A5A] bg-[#FF5A5A]/10'">
                                    {{ mat.note }}/20
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-1.5 mt-2.5">
                                <div class="h-1.5 rounded-full transition-all duration-500"
                                     :class="parseFloat(mat.note) >= 12 ? 'bg-[#05CD99]' : parseFloat(mat.note) >= 10 ? 'bg-[#FFCE20]' : 'bg-[#FF5A5A]'"
                                     :style="{ width: (parseFloat(mat.note) / 20 * 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                    <div class="sticky bottom-0 bg-[#F4F7FE] dark:bg-[#0B1437] p-4 rounded-b-2xl flex justify-end border-t border-gray-100 dark:border-white/5">
                        <button @click="showModal = false"
                                class="px-5 py-2 bg-gradient-to-r from-[#2563EB] to-[#60A5FA] text-white rounded-xl hover:opacity-90 transition text-sm font-semibold shadow-md shadow-blue-500/30">
                            {{ t('close') }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
    <ChatIcon />
    <CommentWall />
</template>

<script setup>
import CommentWall from '@/Components/CommentWall.vue'
import CalculMoyennes from './CalculMoyennes.vue'
import { ref, computed, onMounted } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { useMoyennesStore } from '@/stores/moyennes'
import ChatIcon from '@/Components/ChatIcon.vue'
import axios from 'axios'

const goToPrivateUniversities = () => router.visit(route('etudiant.private-universities'))

const props = defineProps({
    filieres:       { type: Array,  default: () => [] },
    user:           { type: Object, default: () => ({}) },
    questionnaires: { type: Array,  default: () => [] }, 
})

const activeTab   = ref('filieres')
const sidebarOpen = ref(false)

const translations = {
    fr: {
        brand_title: 'Mon Parcours',
        tab_filieres: 'Filières',
        tab_favorites: 'Filières Fav',
        tab_averages: 'Mes Moyennes',
        tab_calcul: 'Calculer ma moyenne',
        tab_settings: 'Paramètres',
        external_private_universities: 'Universités privées',
        my_questionnaires: 'Mes questionnaires',
        empty_questionnaires: 'Aucun questionnaire reçu pour le moment.',
        finished: 'Terminé',
        questionnaire_from: 'De',
        questionnaire_questions_count: 'question(s)',
        questionnaire_received_on: 'Reçu le',
        questionnaire_done: ' Répondu',
        questionnaire_pending: ' À répondre',
        questionnaire_answer: 'Répondre',
        questionnaire_close: 'Fermer',
        questionnaire_already_answered: ' Déjà répondu — vous pouvez modifier vos réponses ci-dessous.',
        questionnaire_placeholder: 'Votre réponse…',
        questionnaire_answered: 'Questions répondues',
        questionnaire_submit: 'Envoyer mes réponses',
        questionnaire_submitting: 'Envoi en cours…',
        questionnaire_errors: 'Erreurs :',
        logout: 'Déconnexion',
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
        settings_subtitle: 'Gérez vos préférences et votre compte',
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
        password_updated: 'Mot de passe modifié avec succès.',
        password_update_error: 'Erreur lors de la modification du mot de passe.',
        account_deleted: 'Compte supprimé avec succès.',
        account_delete_error: 'Impossible de supprimer le compte.',
        // Calculateur
        choose_speciality: 'Choisissez votre spécialité',
        principal_session: 'Principale',
        control_session: 'Contrôle',
        click_to_enter_notes: 'Cliquez pour saisir les notes',
        sport_dispense_label: 'Je suis dispensé(e) de sport',
        sport_dispense_help: 'Note de 0/20 et coefficient exclu.',
        dispensed: 'Dispense',
        results: 'Vos résultats',
        average: 'Moyenne',
        selective_sectors: 'Filières sélectives',
        change_speciality: 'Changer de spécialité',
        calculate_and_continue: 'Calculer et continuer ',
        saving: 'Enregistrement...',
        fill_all_notes: 'Veuillez remplir toutes les notes.',
        congratulations: ' Félicitations !',
        your_average_is: 'Votre moyenne est de',
        keep_it_up: 'Continuez sur votre lancée !',
        go_to_dashboard: 'Accéder au tableau de bord',
        error_occurred: 'Erreur lors de l\'enregistrement.',
        // Double calcul
        control_info_text: 'Cochez les matières principales à repasser et saisissez votre note de contrôle. La meilleure note entre les deux sessions sera retenue.',
        principal_must_be_filled_first: 'Veuillez d\'abord remplir toutes les notes de la session principale.',
        controle_fields_incomplete: 'Cochez au moins une matière éligible et saisissez toutes les notes de contrôle.',
        calculate_principal: 'Calculer (Principale)',
        calculate_control: 'Calculer (Contrôle)',
        result_principal: 'Résultat – Principale',
        result_control: 'Résultat – Contrôle',
        admis: 'Admis(e) ',
        non_admis: 'Non admis(e) ',
        save_and_continue: 'Enregistrer et continuer',
        // Mentions
        refuse: 'Refusé',
        controle: 'Contrôle',
        passable: 'Passable',
        assez_bien: 'Assez bien',
        bien: 'Bien',
        tres_bien: 'Très bien',
    },
    en: {
        brand_title: 'My Path',
        tab_filieres: 'Programs',
        tab_favorites: 'Favorites',
        tab_averages: 'My Averages',
        tab_calcul: 'Calculate average',
        tab_settings: 'Settings',
        external_private_universities: 'Private Universities',
        my_questionnaires: 'My Questionnaires',
        empty_questionnaires: 'No questionnaires received yet.',
        finished: 'Finished',
        questionnaire_from: 'From',
        questionnaire_questions_count: 'question(s)',
        questionnaire_received_on: 'Received on',
        questionnaire_done: ' Answered',
        questionnaire_pending: 'Pending',
        questionnaire_answer: 'Answer',
        questionnaire_close: 'Close',
        questionnaire_already_answered: ' Already answered — you can edit your answers below.',
        questionnaire_placeholder: 'Your answer…',
        questionnaire_answered: 'Answered questions',
        questionnaire_submit: 'Submit my answers',
        questionnaire_submitting: 'Submitting…',
        questionnaire_errors: 'Errors:',
        logout: 'Logout',
        search_placeholder: 'Search (name, code, university, bac type, formula...)',
        available_filieres: 'Available Programs',
        empty_filieres: 'No programs',
        th_filiere: 'Program (License)',
        th_code: 'Code',
        th_etablissement: 'Institution',
        th_parcours: 'Path (Specialty)',
        th_formule: 'Score formula (T)',
        th_specialite: 'Specialty',
        th_universite: 'University',
        th_type_bac: 'Baccalaureate Type',
        th_capacite: 'Capacity',
        th_score_dernier: 'Last oriented score',
        th_action: 'Action',
        favorites_title: 'My Favorite Programs',
        empty_favorites: 'No favorites',
        averages_title: 'My Averages',
        empty_averages: 'No averages saved',
        average_label: 'Average',
        score_x2: 'Score ×2',
        score_plus_7: 'Score +7%',
        coef: 'Coeff',
        close: 'Close',
        settings_title: 'Settings',
        settings_subtitle: 'Manage your preferences and account',
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
        no_licence: 'Unspecified license',
        password_required: 'Password is required.',
        password_updated: 'Password updated successfully.',
        password_update_error: 'Error updating password.',
        account_deleted: 'Account successfully deleted.',
        account_delete_error: 'Could not delete account.',
        choose_speciality: 'Choose your specialty',
        principal_session: 'Main',
        control_session: 'Control',
        click_to_enter_notes: 'Click to enter grades',
        sport_dispense_label: 'I am exempt from sport',
        sport_dispense_help: 'Grade of 0/20 and coefficient excluded.',
        dispensed: 'Exempt',
        results: 'Your results',
        average: 'Average',
        selective_sectors: 'Selective programs',
        change_speciality: 'Change specialty',
        calculate_and_continue: 'Calculate and continue ',
        saving: 'Saving...',
        fill_all_notes: 'Please fill in all grades.',
        congratulations: ' Congratulations!',
        your_average_is: 'Your average is',
        keep_it_up: 'Keep it up!',
        go_to_dashboard: 'Go to dashboard',
        error_occurred: 'An error occurred while saving.',
        control_info_text: 'Check the main subjects to retake and enter your control grade. The best of the two grades will be kept.',
        principal_must_be_filled_first: 'Please fill in all main session grades first.',
        controle_fields_incomplete: 'Check at least one eligible subject and fill in all control grades.',
        calculate_principal: 'Calculate (Main)',
        calculate_control: 'Calculate (Control)',
        result_principal: 'Result – Main',
        result_control: 'Result – Control',
        admis: 'Admitted ',
        non_admis: 'Not admitted ',
        save_and_continue: 'Save and continue',
        refuse: 'Rejected',
        controle: 'Control',
        passable: 'Passable',
        assez_bien: 'Fairly Good',
        bien: 'Good',
        tres_bien: 'Very Good',
    },
    ar: {
        brand_title: 'مساري',
        tab_filieres: 'الشعب',
        tab_favorites: 'المفضلة',
        tab_averages: 'معدلاتي',
        tab_calcul: 'حساب المعدل',
        tab_settings: 'الإعدادات',
        external_private_universities: 'الجامعات الخاصة',
        my_questionnaires: 'استبياناتي',
        empty_questionnaires: 'لا توجد استبيانات بعد.',
        finished: 'مكتمل',
        questionnaire_from: 'من',
        questionnaire_questions_count: 'سؤال',
        questionnaire_received_on: 'تم الاستلام في',
        questionnaire_done: ' تمت الإجابة',
        questionnaire_pending: ' في الانتظار',
        questionnaire_answer: 'الإجابة',
        questionnaire_close: 'إغلاق',
        questionnaire_already_answered: ' تمت الإجابة مسبقا — يمكنك تعديل إجاباتك أدناه.',
        questionnaire_placeholder: 'إجابتك…',
        questionnaire_answered: 'الأسئلة المجاب عنها',
        questionnaire_submit: 'إرسال إجاباتي',
        questionnaire_submitting: 'جار الإرسال…',
        questionnaire_errors: 'أخطاء:',
        logout: 'تسجيل الخروج',
        search_placeholder: 'بحث (اسم، رمز، جامعة، نوع الباك، صيغة...)',
        available_filieres: 'الشعب المتاحة',
        empty_filieres: 'لا توجد شعب',
        th_filiere: 'الشعبة (إجازة)',
        th_code: 'الرمز',
        th_etablissement: 'المؤسسة',
        th_parcours: 'المسار (تخصص)',
        th_formule: 'صيغة النتيجة (ت)',
        th_specialite: 'التخصص',
        th_universite: 'الجامعة',
        th_type_bac: 'نوع البكالوريا',
        th_capacite: 'طاقة الاستيعاب',
        th_score_dernier: 'آخر نتيجة توجيه',
        th_action: 'إجراء',
        favorites_title: 'شعبي المفضلة',
        empty_favorites: 'لا توجد مفضلات',
        averages_title: 'معدلاتي',
        empty_averages: 'لا توجد معدلات مسجلة',
        average_label: 'المعدل',
        score_x2: 'النتيجة ×2',
        score_plus_7: 'النتيجة +7%',
        coef: 'المعامل',
        close: 'إغلاق',
        settings_title: 'الإعدادات',
        settings_subtitle: 'إدارة تفضيلاتك وحسابك',
        language_title: 'اللغة',
        theme_title: 'المظهر',
        theme_light: 'فاتح',
        theme_dark: 'داكن',
        security_title: 'الأمان',
        current_password: 'كلمة المرور الحالية',
        new_password: 'كلمة المرور الجديدة',
        confirm_new_password: 'تأكيد كلمة المرور الجديدة',
        update_password: 'تغيير كلمة المرور',
        account_title: 'الحساب',
        delete_account: 'حذف الحساب',
        delete_warning: 'هذا الإجراء لا رجعة فيه. أدخل كلمة مرورك للتأكيد.',
        password: 'كلمة المرور',
        confirm_delete: 'تأكيد الحذف',
        cancel: 'إلغاء',
        no_category: 'شعب عامة',
        no_licence: 'إجازة غير محددة',
        password_required: 'كلمة المرور مطلوبة.',
        password_updated: 'تم تغيير كلمة المرور بنجاح.',
        password_update_error: 'خطأ في تغيير كلمة المرور.',
        account_deleted: 'تم حذف الحساب بنجاح.',
        account_delete_error: 'تعذر حذف الحساب.',
        choose_speciality: 'اختر تخصصك',
        principal_session: 'الرئيسية',
        control_session: 'المراقبة',
        click_to_enter_notes: 'انقر لإدخال النقاط',
        sport_dispense_label: 'أنا معفى من الرياضة',
        sport_dispense_help: 'تمنح نقطة 0/20 ويُستثنى المعامل.',
        dispensed: 'إعفاء',
        results: 'نتائجك',
        average: 'المعدل',
        selective_sectors: 'شعب انتقائية',
        change_speciality: 'تغيير التخصص',
        calculate_and_continue: 'احسب وتابع ',
        saving: 'جاري الحفظ...',
        fill_all_notes: 'يرجى ملء جميع النقاط.',
        congratulations: ' مبروك!',
        your_average_is: 'معدلك هو',
        keep_it_up: 'واصل على هذا المنوال!',
        go_to_dashboard: 'الذهاب إلى لوحة القيادة',
        error_occurred: 'حدث خطأ أثناء الحفظ.',
        control_info_text: 'حدد المواد الرئيسية التي تريد إعادتها وأدخل نقطة المراقبة. سيتم الاحتفاظ بأفضل نقطة بين الدورتين.',
        principal_must_be_filled_first: 'يرجى ملء جميع نقاط الدورة الرئيسية أولاً.',
        controle_fields_incomplete: 'حدد مادة مؤهلة واحدة على الأقل وأدخل جميع نقاط المراقبة.',
        calculate_principal: 'احسب (الرئيسية)',
        calculate_control: 'احسب (المراقبة)',
        result_principal: 'النتيجة - الرئيسية',
        result_control: 'النتيجة - المراقبة',
        admis: 'مقبول ',
        non_admis: 'غير مقبول ',
        save_and_continue: 'حفظ ومتابعة',
        refuse: 'مرفوض',
        controle: 'مراقبة',
        passable: 'مقبول',
        assez_bien: 'حسن',
        bien: 'جيد',
        tres_bien: 'جيد جدا',
    }
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
const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' })

const currentLocale = computed(() => translations[selectedLanguage.value] || translations.fr)
const t = (key) => currentLocale.value[key] || translations.fr[key] || key

const tabs = computed(() => ([
    { id: 'filieres',        label: t('tab_filieres') },
    { id: 'favoris',         label: t('tab_favorites') },
    { id: 'questionnaires',  label: t('my_questionnaires') },
    { id: 'mesMoyennes',     label: t('tab_averages') },
    { id: 'calcul',          label: t('tab_calcul') },
    { id: 'parametres',      label: t('tab_settings') },
]))

const languageOptions = [
    { value: 'fr', label: 'Français' },
    { value: 'ar', label: 'Arabe' },
    { value: 'en', label: 'Anglais' },
]

const moyennesStore = useMoyennesStore()
onMounted(() => {
    moyennesStore.fetchMoyennes()
    const saved = localStorage.getItem('favoriteFilieres')
    if (saved) { try { favoriteFilieres.value = JSON.parse(saved) } catch (e) { favoriteFilieres.value = [] } }
    sanitizeFavorites()
    initSettings()
})

const search           = ref('')
const favoriteFilieres = ref([])

const ARABIC_RE = /[\u0600-\u06FF\uFB50-\uFDFF\uFE70-\uFEFF]/
const isArabicText = (text) => { if (!text) return false; return ARABIC_RE.test(text) }

const filteredFilieres = computed(() => {
    if (!search.value) return props.filieres
    const s = search.value.toLowerCase()
    return props.filieres.filter(f => {
        const searchable = [f.specialite, f.nom, f.licence, f.code, f.universite, f.type_bac, f.formule, f.capacite, f.score_dernier_oriente_2025, ...(Array.isArray(f.criteres) ? f.criteres : [])]
            .filter(v => v !== null && v !== undefined).map(v => String(v).toLowerCase()).join(' ')
        return searchable.indexOf(s) !== -1
    })
})

const favFilieresData = computed(() => props.filieres.filter(f => favoriteFilieres.value.includes(f.id)))
const favoriteCount = computed(() => favFilieresData.value.length)

const groupedFilieres = computed(() => {
    const critMap = new Map()
    for (const f of filteredFilieres.value) {
        const critLabel = Array.isArray(f.criteres) && f.criteres.length ? String(f.criteres[0]) : t('no_category')
        if (!critMap.has(critLabel)) critMap.set(critLabel, new Map())
        const licMap = critMap.get(critLabel)
        const licLabel = (f.licence || f.specialite || f.nom || t('no_licence'))
        if (!licMap.has(licLabel)) licMap.set(licLabel, [])
        licMap.get(licLabel).push(f)
    }
    return Array.from(critMap.entries()).map(([label, licMap]) => ({
        label, licences: Array.from(licMap.entries()).map(([licence, rows]) => ({ label: licence, rows })),
    }))
})

const isFav = (id) => favoriteFilieres.value.includes(id)
const toggleFav = (id) => {
    const i = favoriteFilieres.value.indexOf(id)
    i === -1 ? favoriteFilieres.value.push(id) : favoriteFilieres.value.splice(i, 1)
    sanitizeFavorites()
}

function sanitizeFavorites() {
    const validIds = new Set(props.filieres.map(f => f.id))
    favoriteFilieres.value = favoriteFilieres.value.filter(id => validIds.has(id))
    localStorage.setItem('favoriteFilieres', JSON.stringify(favoriteFilieres.value))
}

const showModal = ref(false)
const selectedM = ref(null)
const openModal = (m) => { selectedM.value = m; showModal.value = true }
const openedQuestionnaireId = ref(null)
const answers = ref({})
const multipleAnswers = ref({})
const questionnaireForm = useForm({ reponses: [] })

const getIntlLocale = () => { if (selectedLanguage.value === 'en') return 'en-US'; if (selectedLanguage.value === 'ar') return 'ar'; return 'fr-FR' }
const fmtDate = (d) => new Date(d).toLocaleDateString(getIntlLocale(), { day: '2-digit', month: '2-digit', year: 'numeric' })

const logout = () => router.post('/logout')

const initSettings = async () => {
    const localLanguage = localStorage.getItem('language')
    if (localLanguage && languageOptions.some(o => o.value === localLanguage)) applyLanguage(localLanguage)
    const theme = localStorage.getItem('theme')
    theme === 'dark' ? setTheme('dark') : setTheme('light')
    try {
        const { data } = await axios.get(route('language.current'))
        if (languageOptions.some(o => o.value === data.language)) applyLanguage(data.language)
    } catch { applyLanguage('fr') }
}

const changeLanguage = async (language) => {
    const prev = selectedLanguage.value; applyLanguage(language)
    try { await axios.put(route('language.update'), { language }) } catch { applyLanguage(prev) }
}

const applyLanguage = (language) => {
    selectedLanguage.value = language; localStorage.setItem('language', language)
    document.documentElement.setAttribute('lang', language)
    document.documentElement.setAttribute('dir', language === 'ar' ? 'rtl' : 'ltr')
}

const setTheme = (theme) => {
    selectedTheme.value = theme
    theme === 'dark' ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', theme)
}

const updatePassword = async () => {
    securityMessage.value = ''; passwordLoading.value = true
    try {
        const { data } = await axios.put(route('password.update'), passwordForm.value)
        securityMessageType.value = 'success'; securityMessage.value = data.message || t('password_updated')
        passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    } catch (error) {
        securityMessageType.value = 'error'; securityMessage.value = error?.response?.data?.message || t('password_update_error')
    } finally { passwordLoading.value = false }
}

const deleteAccount = async () => {
    deleteMessage.value = ''
    if (!deletePassword.value) { deleteMessageType.value = 'error'; deleteMessage.value = t('password_required'); return }
    deleteLoading.value = true
    try {
        const { data } = await axios.delete(route('account.destroy'), { data: { password: deletePassword.value } })
        deleteMessageType.value = 'success'; deleteMessage.value = data.message || t('account_deleted')
        setTimeout(() => { router.visit(route('login')) }, 700)
    } catch (error) {
        deleteMessageType.value = 'error'; deleteMessage.value = error?.response?.data?.message || t('account_delete_error')
    } finally { deleteLoading.value = false }
}

function formatDate(d) {
    if (!d) return ''
    return new Date(d).toLocaleDateString(getIntlLocale(), { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function categoryLabel(categorie) {
    if (categorie === 'competence') return ' Compétence'
    if (categorie === 'preference') return ' Préférence'
    return ' Intérêt'
}

function toggleQuestionnaire(questionnaire) {
    if (openedQuestionnaireId.value === questionnaire.id) {
        openedQuestionnaireId.value = null
        return
    }
    openedQuestionnaireId.value = questionnaire.id
    questionnaireForm.clearErrors()
}

function toggleMultiple(questionId, option) {
    if (!multipleAnswers.value[questionId]) multipleAnswers.value[questionId] = []
    const idx = multipleAnswers.value[questionId].indexOf(option)
    if (idx === -1) {
        multipleAnswers.value[questionId].push(option)
    } else {
        multipleAnswers.value[questionId].splice(idx, 1)
    }
    answers.value[questionId] = multipleAnswers.value[questionId].join(', ')
}

function answeredCount(questionnaire) {
    const questions = questionnaire?.questions || []
    return questions.filter((question) => {
        if (question.type === 'choix_multiple') {
            return (multipleAnswers.value[question.id] || []).length > 0
        }
        return Boolean(answers.value[question.id]?.toString().trim())
    }).length
}

function progressPercent(questionnaire) {
    const total = questionnaire?.questions?.length || 0
    if (!total) return 0
    return (answeredCount(questionnaire) / total) * 100
}

function submitQuestionnaire(questionnaire) {
    const reponses = (questionnaire?.questions || [])
        .map((question) => {
            const value = question.type === 'choix_multiple'
                ? (multipleAnswers.value[question.id] || []).join(', ')
                : (answers.value[question.id]?.toString().trim() || '')
            if (!value) return null
            return { question_id: question.id, reponse: value }
        })
        .filter(Boolean)

    if (!reponses.length) return

    questionnaireForm.reponses = reponses
    questionnaireForm.post(route('etudiant.questionnaires.soumettre', questionnaire.id), {
        preserveScroll: true,
        onSuccess: () => {
            openedQuestionnaireId.value = null
            answers.value = {}
            multipleAnswers.value = {}
            activeTab.value = 'questionnaires'
        },
    })
}
</script>

<style scoped>
* { transition: background-color 0.2s ease, border-color 0.2s ease, color 0.15s ease; }
.font-ar { font-family: 'Noto Sans Arabic', 'Segoe UI', sans-serif; }
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #60A5FA40; border-radius: 99px; }
::-webkit-scrollbar-thumb:hover { background: #60A5FA80; }
</style>