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
                        <h1 class="text-2xl font-bold mb-6">Demandes d'inscription en attente</h1>
                        
                        <!-- Compteur -->
                        <div class="mb-6">
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            <strong>{{ pendingUsers.length }}</strong> demande(s) en attente de validation
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Liste des demandes -->
                        <div v-if="pendingUsers.length > 0" class="overflow-x-auto">
                            <table class="min-w-full bg-white border">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date d'inscription</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in pendingUsers" :key="user.id" class="border-t hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ user.name }}</td>
                                        <td class="px-6 py-4">{{ user.email }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="{
                                                'text-blue-600 font-bold': user.role === 'conseiller',
                                                'text-green-600': user.role === 'etudiant'
                                            }">
                                                {{ user.role === 'conseiller' ? 'Conseiller' : 'Étudiant' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">{{ formatDate(user.created_at) }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <button 
                                                    @click="approveUser(user.id)"
                                                    :disabled="processing === user.id"
                                                    class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 disabled:opacity-50 transition">
                                                    {{ processing === user.id ? 'Traitement...' : 'Accepter' }}
                                                </button>
                                                <button 
                                                    @click="rejectUser(user.id)"
                                                    :disabled="processing === user.id"
                                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 disabled:opacity-50 transition">
                                                    {{ processing === user.id ? 'Traitement...' : 'Refuser' }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="mt-2 text-gray-500">Aucune demande en attente</p>
                            <p class="text-sm text-gray-400">Les nouvelles inscriptions apparaîtront ici</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const page = usePage()
const props = defineProps({
    pendingUsers: Array
})

const processing = ref(null)

function approveUser(id) {
    if (confirm('Accepter cette demande d\'inscription ?')) {
        processing.value = id
        router.post(route('admin.approve', id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                processing.value = null
                // Recharger la page pour voir les changements
                router.reload()
            },
            onError: (errors) => {
                processing.value = null
                console.error('Erreur:', errors)
            }
        })
    }
}

function rejectUser(id) {
    if (confirm('Refuser cette demande d\'inscription ?')) {
        processing.value = id
        router.post(route('admin.reject', id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                processing.value = null
                router.reload()
            },
            onError: (errors) => {
                processing.value = null
                console.error('Erreur:', errors)
            }
        })
    }
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>