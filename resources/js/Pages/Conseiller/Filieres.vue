<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Messages flash -->
                <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-300 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-300 rounded">
                    {{ $page.props.flash.error }}
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-300">
                    <div class="p-6 text-gray-900 dark:text-gray-100">

                        <!-- Import PDF -->
                        <div class="mb-8 bg-amber-50 dark:bg-amber-900/20 p-6 rounded-lg border border-amber-200 dark:border-amber-700">
                            <h2 class="text-lg font-semibold mb-4 dark:text-gray-200">Remplacer toutes les filières depuis PDF</h2>
                            <p class="text-sm mb-3 text-gray-700 dark:text-gray-300">
                                Cette action supprime toutes les filières existantes puis importe le guide PDF nettoyé.
                            </p>
                            <form @submit.prevent="importPdf" class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Chemin PDF (option 1)</label>
                                    <input
                                        type="text"
                                        v-model="importForm.pdf_path"
                                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-400 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                        placeholder="C:\\...\\guide.pdf"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Ou téléverser PDF (option 2)</label>
                                    <input type="file" accept=".pdf" @change="handlePdfFile" class="block w-full text-sm dark:text-gray-300">
                                </div>
                                <button
                                    type="submit"
                                    :disabled="importForm.processing"
                                    class="bg-amber-700 hover:bg-amber-800 text-white px-5 py-2 rounded-full disabled:opacity-50 transition">
                                    {{ importForm.processing ? 'Import en cours...' : 'Remplacer toutes les filières' }}
                                </button>
                            </form>
                        </div>

                        <!-- Import CSV -->
                        <div class="mb-8 bg-emerald-50 dark:bg-emerald-900/20 p-6 rounded-lg border border-emerald-200 dark:border-emerald-700">
                            <h2 class="text-lg font-semibold mb-4 dark:text-gray-200">Remplacer toutes les filières depuis CSV (recommandé)</h2>
                            <p class="text-sm mb-3 text-gray-700 dark:text-gray-300">
                                Utilise ton fichier CSV en français (Filière/licence, Code, Établissement, Parcours, Série, Formule, Conditions, Bandeau, Score dernier orienté).
                            </p>
                            <form @submit.prevent="importCsv" class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Chemin CSV (option 1)</label>
                                    <input
                                        type="text"
                                        v-model="csvForm.csv_path"
                                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                        placeholder="C:\\...\\filieres.csv"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Ou téléverser CSV (option 2)</label>
                                    <input type="file" accept=".csv,.txt" @change="handleCsvFile" class="block w-full text-sm dark:text-gray-300">
                                </div>
                                <button
                                    type="submit"
                                    :disabled="csvForm.processing"
                                    class="bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2 rounded-full disabled:opacity-50 transition">
                                    {{ csvForm.processing ? 'Import CSV en cours...' : 'Remplacer toutes les filières (CSV)' }}
                                </button>
                            </form>
                        </div>
                        
                        <!-- Ajouter une filière -->
                        <div class="mb-8 bg-gray-50 dark:bg-gray-700 p-6 rounded-lg transition-colors duration-300">
                            <h2 class="text-lg font-semibold mb-4 dark:text-gray-200">Ajouter une nouvelle filière</h2>
                            <form @submit.prevent="addFiliere" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Nom de la filière *</label>
                                    <input 
                                        type="text" 
                                        v-model="form.name" 
                                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                        placeholder="Ex: Informatique, Génie Civil, etc."
                                        required
                                    >
                                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.name }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Code (optionnel)</label>
                                    <input 
                                        type="text" 
                                        v-model="form.code" 
                                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                        placeholder="Ex: INFO, GENIE, etc."
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Licence / شعبة (optionnel)</label>
                                    <input
                                        type="text"
                                        v-model="form.licence"
                                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                        placeholder="الإجازة/الشعبة"
                                    >
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Université (optionnel)</label>
                                    <input 
                                        type="text" 
                                        v-model="form.universite" 
                                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                        placeholder="Nom de l'université"
                                    >
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Type de Bac (optionnel)</label>
                                    <input 
                                        type="text" 
                                        v-model="form.type_bac" 
                                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                        placeholder="Ex: Mathématiques, Sciences, etc."
                                    >
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Formule (optionnel)</label>
                                    <input 
                                        type="text" 
                                        v-model="form.formule" 
                                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                        placeholder="Formule de calcul"
                                    >
                                </div>
                                
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2 rounded-full disabled:opacity-50 transition">
                                    {{ form.processing ? 'Ajout...' : '+ Ajouter la filière' }}
                                </button>
                            </form>
                        </div>
                        
                        <!-- Recherche -->
                        <div class="mb-4">
                            <input v-model="searchQuery" type="text"
                                   placeholder="Rechercher (licence, code, université, type bac, bandeau...)"
                                   class="w-full max-w-md p-3 border rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"/>
                        </div>

                        <!-- Liste des filières -->
                        <div>
                            <h2 class="text-lg font-semibold mb-4 dark:text-gray-200">
                                Liste des filières 
                                <span class="text-sm font-normal text-gray-500">({{ filieres.length }} filières)</span>
                            </h2>
                            <div v-if="filieres.length > 0" class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 border dark:border-gray-700">
                                    <thead>
                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Licence (الإجازة)</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Université</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Spécialité</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Code</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type bac (نوع الباك)</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Formule</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Bandeau (critères)</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Capacité</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">مجموع آخر موجه 2024</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="(group, gIdx) in groupedFilieres" :key="`g-${gIdx}`">
                                            <tr class="bg-indigo-50/80 dark:bg-indigo-900/30 border-t-2 border-indigo-300 dark:border-indigo-700">
                                                <td colspan="11" class="px-4 py-2 font-bold text-indigo-800 dark:text-indigo-200 text-sm">
                                                    <span class="inline-block w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: bandeauColor(gIdx) }"></span>
                                                    {{ group.label }}
                                                    <span class="text-xs font-normal text-indigo-500 ml-2">({{ group.rows.length }} filières)</span>
                                                </td>
                                            </tr>
                                            <tr v-for="filiere in group.rows" :key="filiere.id" 
                                                class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                <td class="px-4 py-3 dark:text-gray-300 text-sm">{{ filiere.id }}</td>
                                                <td class="px-4 py-3 dark:text-gray-300 text-sm" :dir="isArabic(filiere.licence) ? 'rtl' : 'ltr'">{{ filiere.licence || '-' }}</td>
                                                <td class="px-4 py-3 dark:text-gray-300 text-sm" :dir="isArabic(filiere.universite) ? 'rtl' : 'ltr'">{{ filiere.universite || '-' }}</td>
                                                <td class="px-4 py-3 font-medium dark:text-gray-200 text-sm" :dir="isArabic(filiere.specialite) ? 'rtl' : 'ltr'">{{ filiere.specialite || '-' }}</td>
                                                <td class="px-4 py-3 dark:text-gray-300 text-sm">{{ filiere.code || '-' }}</td>
                                                <td class="px-4 py-3 dark:text-gray-300 text-sm" :dir="isArabic(filiere.type_bac) ? 'rtl' : 'ltr'">{{ filiere.type_bac || '-' }}</td>
                                                <td class="px-4 py-3 dark:text-gray-300 text-sm">{{ filiere.formule || '-' }}</td>
                                                <td class="px-4 py-3 dark:text-gray-300 text-sm" :dir="isArabic(criteresLabel(filiere)) ? 'rtl' : 'ltr'">
                                                    <span v-if="filiere.criteres && filiere.criteres.length" 
                                                          class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                                        {{ criteresLabel(filiere) }}
                                                    </span>
                                                    <span v-else class="text-gray-400">-</span>
                                                </td>
                                                <td class="px-4 py-3 dark:text-gray-300 text-sm">{{ filiere.capacite || '-' }}</td>
                                                <td class="px-4 py-3 dark:text-gray-300 text-sm font-semibold">
                                                    <span v-if="filiere.score_dernier_oriente_2025" class="text-emerald-600 dark:text-emerald-400">
                                                        {{ filiere.score_dernier_oriente_2025 }}
                                                    </span>
                                                    <span v-else class="text-gray-400">-</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <button
                                                        @click="startEdit(filiere)"
                                                        class="mr-2 px-3 py-1 border-2 border-indigo-700 dark:border-indigo-400 text-indigo-700 dark:text-indigo-300 rounded-full hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-sm transition">
                                                        Modifier
                                                    </button>
                                                    <button 
                                                        @click="deleteFiliere(filiere.id)"
                                                        :disabled="deleting === filiere.id"
                                                        class="px-3 py-1 border-2 border-red-700 dark:border-red-400 text-red-700 dark:text-red-300 rounded-full hover:bg-red-50 dark:hover:bg-red-900/30 disabled:opacity-50 text-sm transition">
                                                        {{ deleting === filiere.id ? '...' : 'Supprimer' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                <p class="mt-2">Aucune filière pour le moment</p>
                                <p class="text-sm">Ajoutez votre première filière ci-dessus</p>
                            </div>
                        </div>

                        <!-- Formulaire d'édition -->
                        <div v-if="editing" class="mt-8 bg-indigo-50 dark:bg-indigo-900/20 p-6 rounded-lg border border-indigo-200 dark:border-indigo-700">
                            <h2 class="text-lg font-semibold mb-4 dark:text-gray-200">Modifier la filière #{{ editing.id }}</h2>
                            <form @submit.prevent="saveEdit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input v-model="editing.name" required class="border rounded p-2 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Nom">
                                <input v-model="editing.licence" class="border rounded p-2 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Licence / شعبة">
                                <input v-model="editing.code" class="border rounded p-2 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Code">
                                <input v-model="editing.universite" class="border rounded p-2 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Université">
                                <input v-model="editing.type_bac" class="border rounded p-2 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Type bac">
                                <input v-model="editing.formule" class="border rounded p-2 dark:bg-gray-800 dark:border-gray-600 dark:text-white" placeholder="Formule">
                                <div class="md:col-span-2 flex gap-2">
                                    <button type="submit" class="bg-indigo-700 hover:bg-indigo-800 text-white px-5 py-2 rounded-full transition">
                                        Enregistrer
                                    </button>
                                    <button type="button" @click="cancelEdit" class="bg-gray-700 hover:bg-gray-800 text-white px-5 py-2 rounded-full transition">
                                        Annuler
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
// Le script reste identique à votre version précédente
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    filieres: Array
})

const form = useForm({
    name: '',
    licence: '',
    code: '',
    universite: '',
    type_bac: '',
    formule: '',
})

const deleting = ref(null)
const editing = ref(null)
const searchQuery = ref('')

const importForm = useForm({
    pdf_path: '',
    pdf_file: null,
})

const csvForm = useForm({
    csv_path: '',
    csv_file: null,
})

const ARABIC_REGEX = /[\u0600-\u06FF\uFB50-\uFDFF\uFE70-\uFEFF]/

function isArabic(text) {
    if (!text) return false
    return ARABIC_REGEX.test(text)
}

function criteresLabel(filiere) {
    if (!filiere.criteres || !Array.isArray(filiere.criteres) || filiere.criteres.length === 0) return ''
    return filiere.criteres[0]
}

const BANDEAU_COLORS = [
    '#6366f1', '#ec4899', '#f59e0b', '#10b981', '#8b5cf6',
    '#ef4444', '#06b6d4', '#84cc16', '#f97316', '#14b8a6',
]

function bandeauColor(idx) {
    return BANDEAU_COLORS[idx % BANDEAU_COLORS.length]
}

const filteredFilieres = computed(() => {
    if (!searchQuery.value) return props.filieres
    const s = searchQuery.value.toLowerCase()
    return props.filieres.filter(f => {
        const searchable = [
            f.specialite, f.nom, f.licence, f.code, f.universite,
            f.type_bac, f.formule, f.capacite, f.score_dernier_oriente_2025,
            ...(Array.isArray(f.criteres) ? f.criteres : []),
        ]
            .filter(v => v !== null && v !== undefined)
            .map(v => String(v).toLowerCase())
            .join(' ')
        return searchable.includes(s)
    })
})

const groupedFilieres = computed(() => {
    const map = new Map()
    for (const f of filteredFilieres.value) {
        const label = (Array.isArray(f.criteres) && f.criteres.length)
            ? String(f.criteres[0])
            : 'Filières générales'
        if (!map.has(label)) map.set(label, [])
        map.get(label).push(f)
    }
    return Array.from(map.entries()).map(([label, rows]) => ({ label, rows }))
})

function addFiliere() {
    form.post(route('conseiller.filieres.store'), {
        onSuccess: () => {
            form.reset()
        }
    })
}

function startEdit(filiere) {
    editing.value = {
        id: filiere.id,
        name: filiere.specialite || '',
        licence: filiere.licence || '',
        code: filiere.code || '',
        universite: filiere.universite || '',
        type_bac: filiere.type_bac || '',
        formule: filiere.formule || '',
    }
}

function cancelEdit() {
    editing.value = null
}

function saveEdit() {
    if (!editing.value) return
    router.put(route('conseiller.filieres.update', editing.value.id), editing.value, {
        onSuccess: () => (editing.value = null),
    })
}

function handlePdfFile(event) {
    importForm.pdf_file = event.target.files?.[0] || null
}

function importPdf() {
    if (!confirm('Cela supprimera toutes les filières actuelles et les remplacera par celles du PDF. Continuer ?')) return
    importForm.post(route('conseiller.filieres.import-pdf'), {
        forceFormData: true,
        onSuccess: () => {
            importForm.pdf_file = null
        },
    })
}

function handleCsvFile(event) {
    csvForm.csv_file = event.target.files?.[0] || null
}

function importCsv() {
    if (!confirm('Cela supprimera toutes les filières actuelles et les remplacera par celles du CSV. Continuer ?')) return
    csvForm.post(route('conseiller.filieres.import-csv'), {
        forceFormData: true,
        onSuccess: () => {
            csvForm.csv_file = null
        },
    })
}

function deleteFiliere(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette filière ?')) {
        deleting.value = id
        router.delete(route('conseiller.filieres.destroy', id), {
            onSuccess: () => {
                deleting.value = null
            },
            onError: () => {
                deleting.value = null
                alert('Erreur lors de la suppression')
            }
        })
    }
}
</script>