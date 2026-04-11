<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Messages flash -->
                <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
                    {{ $page.props.flash.error }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold">Gestion des utilisateurs</h1>
                            
                        </div>

                        <!-- Filtres par rôle -->
                        <div class="mb-6 flex space-x-2">
                            <button 
                                @click="filterRole = 'all'"
                                :class="[
                                    'px-3 py-1 rounded transition',
                                    filterRole === 'all' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                ]">
                                Tous
                            </button>
                            <button 
                                @click="filterRole = 'admin'"
                                :class="[
                                    'px-3 py-1 rounded transition',
                                    filterRole === 'admin' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                ]">
                                Admins
                            </button>
                            <button 
                                @click="filterRole = 'conseiller'"
                                :class="[
                                    'px-3 py-1 rounded transition',
                                    filterRole === 'conseiller' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                ]">
                                Conseillers
                            </button>
                            <button 
                                @click="filterRole = 'etudiant'"
                                :class="[
                                    'px-3 py-1 rounded transition',
                                    filterRole === 'etudiant' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                ]">
                                Étudiants
                            </button>
                        </div>

                        <!-- Compteur -->
                        <div class="mb-4 text-sm text-gray-600">
                            Total: {{ filteredUsers.length }} utilisateur(s)
                        </div>

                        <!-- Liste des utilisateurs -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date d'inscription</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in filteredUsers" :key="user.id" class="border-t hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ user.id }}</td>
                                        <td class="px-6 py-4">{{ user.name }}</td>
                                        <td class="px-6 py-4">{{ user.email }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="{
                                                'text-red-600 font-bold': user.role === 'admin',
                                                'text-blue-600': user.role === 'conseiller',
                                                'text-green-600': user.role === 'etudiant'
                                            }">
                                                {{ getRoleLabel(user.role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="{
                                                'text-green-600': user.status === 'approved',
                                                'text-yellow-600': user.status === 'pending',
                                                'text-red-600': user.status === 'rejected'
                                            }">
                                                {{ getStatusLabel(user.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">{{ formatDate(user.created_at) }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a :href="route('admin.users.edit', user.id)" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                    Modifier
                                                </a>
                                                <button 
                                                    v-if="user.id !== currentUserId"
                                                    @click="deleteUser(user.id)"
                                                    :disabled="deleting === user.id"
                                                    class="text-red-600 hover:text-red-900 disabled:opacity-50">
                                                    {{ deleting === user.id ? '...' : 'Supprimer' }}
                                                </button>
                                                <span v-else class="text-gray-400 text-sm">Vous</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div v-if="filteredUsers.length === 0" class="text-center py-8 text-gray-500">
                            Aucun utilisateur trouvé
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const page = usePage()
const props = defineProps({
    users: Array
})

const filterRole = ref('all')
const deleting = ref(null)
const currentUserId = computed(() => page.props.auth.user.id)

const filteredUsers = computed(() => {
    if (filterRole.value === 'all') {
        return props.users || []
    }
    return (props.users || []).filter(user => user.role === filterRole.value)
})

function getRoleLabel(role) {
    const roles = {
        admin: 'Admin',
        conseiller: 'Conseiller',
        etudiant: 'Étudiant'
    }
    return roles[role] || role
}

function getStatusLabel(status) {
    const statuses = {
        approved: 'Approuvé',
        pending: 'En attente',
        rejected: 'Refusé'
    }
    return statuses[status] || status
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR')
}

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