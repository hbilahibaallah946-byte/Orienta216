<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Notification des demandes en attente -->
                <div v-if="pendingCount > 0" class="mb-6">
                    <Link :href="route('admin.pending-requests')" 
                          class="block bg-yellow-50 border-l-4 border-yellow-400 p-4 hover:bg-yellow-100">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>{{ pendingCount }}</strong> demande(s) d'inscription en attente de validation
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
                
                <!-- Le reste du dashboard -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Dashboard Administrateur</h1>
                        <!-- ... vos stats existantes ... -->
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    <AdminCommentPanel />  
</template>

<script setup>
import AdminCommentPanel from '@/Components/AdminCommentPanel.vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({
    totalUsers: Number,
    totalEtudiants: Number,
    totalConseillers: Number,
    totalFilieres: Number,
    recentUsers: Array,
    pendingCount: Number  // ← Ajouter cette prop
})
</script>