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
                        <h1 class="text-2xl font-bold mb-6">Gestion des filières</h1>
                        
                        <!-- Formulaire d'ajout -->
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
                                
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Année (optionnel)</label>
                                    <input 
                                        type="number" 
                                        v-model="form.annee" 
                                        class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                        placeholder="2024"
                                    >
                                </div>
                                
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2 rounded disabled:opacity-50 transition">
                                    {{ form.processing ? 'Ajout...' : '+ Ajouter la filière' }}
                                </button>
                            </form>
                        </div>
                        
                        <!-- Liste des filières -->
                        <div>
                            <h2 class="text-lg font-semibold mb-4 dark:text-gray-200">Liste des filières</h2>
                            <div v-if="filieres.length > 0" class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 border dark:border-gray-700">
                                    <thead>
                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Spécialité</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Code</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Université</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type Bac</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Formule</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Année</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="filiere in filieres" :key="filiere.id" class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-6 py-4 dark:text-gray-300">{{ filiere.id }}</td>
                                            <td class="px-6 py-4 font-medium dark:text-gray-200">{{ filiere.specialite }}</td>
                                            <td class="px-6 py-4 dark:text-gray-300">{{ filiere.code || '-' }}</td>
                                            <td class="px-6 py-4 dark:text-gray-300">{{ filiere.universite || '-' }}</td>
                                            <td class="px-6 py-4 dark:text-gray-300">{{ filiere.type_bac || '-' }}</td>
                                            <td class="px-6 py-4 dark:text-gray-300">{{ filiere.formule || '-' }}</td>
                                            <td class="px-6 py-4 dark:text-gray-300">{{ filiere.annee || '-' }}</td>
                                            <td class="px-6 py-4">
                                                <button 
                                                    @click="deleteFiliere(filiere.id)"
                                                    :disabled="deleting === filiere.id"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 disabled:opacity-50">
                                                    {{ deleting === filiere.id ? '...' : 'Supprimer' }}
                                                </button>
                                            </td>
                                        </tr>
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
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    filieres: Array
})

const form = useForm({
    name: '',
    code: '',
    universite: '',
    type_bac: '',
    formule: '',
    annee: new Date().getFullYear()
})

const deleting = ref(null)

function addFiliere() {
    form.post(route('conseiller.filieres.store'), {
        onSuccess: () => {
            form.reset()
            form.annee = new Date().getFullYear()
        }
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