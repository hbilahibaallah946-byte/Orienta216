<!-- resources/js/Pages/Conseiller/Etudiants.vue -->
<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-300">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h1 class="text-2xl font-bold mb-6">Liste des étudiants</h1>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white dark:bg-gray-800 border dark:border-gray-700">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-700">
                                        <th class="px-6 py-3 text-left text-gray-700 dark:text-gray-200">Nom</th>
                                        <th class="px-6 py-3 text-left text-gray-700 dark:text-gray-200">Email</th>
                                        <th class="px-6 py-3 text-left text-gray-700 dark:text-gray-200">Filière</th>
                                        <th class="px-6 py-3 text-left text-gray-700 dark:text-gray-200">Date d'inscription</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="etudiant in etudiants" :key="etudiant.id" class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <!-- Nom cliquable -->
                                        <td class="px-6 py-4">
                                            <Link :href="route('conseiller.etudiants.show', etudiant.id)" 
                                                  class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                                {{ etudiant.name }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 dark:text-gray-300">{{ etudiant.email }}</td>
                                        <td class="px-6 py-4 dark:text-gray-300">{{ etudiant.filiere?.specialite || 'Non assigné' }}</td>
                                        <td class="px-6 py-4 dark:text-gray-300">{{ new Date(etudiant.created_at).toLocaleDateString() }}</td>
                                        <td class="px-6 py-4">
                                            
                                        </td>
                                    </tr>
                                    <tr v-if="!etudiants || etudiants.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Aucun étudiant trouvé
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({
    etudiants: Array
})
</script>