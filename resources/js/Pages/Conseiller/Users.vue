<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-300">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold">Gestion des utilisateurs</h1>
                            <a :href="route('admin.users.create')" 
                               class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">
                                + Nouvel utilisateur
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nom</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Rôle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="user in users" :key="user.id">
                                        <td class="px-6 py-4 dark:text-gray-300">{{ user.name }}</td>
                                        <td class="px-6 py-4 dark:text-gray-300">{{ user.email }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="{
                                                'text-red-600 dark:text-red-400 font-bold': user.role === 'admin',
                                                'text-blue-600 dark:text-blue-400': user.role === 'conseiller',
                                                'text-green-600 dark:text-green-400': user.role === 'etudiant'
                                            }">
                                                {{ user.role === 'admin' ? 'Admin' : user.role === 'conseiller' ? 'Conseiller' : 'Étudiant' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a :href="route('admin.users.edit', user.id)" 
                                               class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-3">
                                                Modifier
                                            </a>
                                            <button 
                                                @click="deleteUser(user.id)"
                                                :disabled="deleting === user.id"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 disabled:opacity-50">
                                                {{ deleting === user.id ? 'Suppression...' : 'Supprimer' }}
                                            </button>
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
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    users: Array
})

const deleting = ref(null)

function deleteUser(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        deleting.value = id
        router.delete(route('admin.users.destroy', id), {
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