<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Gestion des filières</h1>
                        
                        <!-- Formulaire d'ajout -->
                        <form @submit.prevent="addFiliere" class="mb-8">
                            <div class="flex gap-4">
                                <input 
                                    type="text" 
                                    v-model="newFiliereName" 
                                    placeholder="Nom de la filière"
                                    class="flex-1 border rounded-lg p-2"
                                    required
                                >
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    Ajouter
                                </button>
                            </div>
                        </form>
                        
                        <!-- Liste des filières -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-6 py-3 text-left">Nom</th>
                                        <th class="px-6 py-3 text-left">Date de création</th>
                                        <th class="px-6 py-3 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="filiere in filieres" :key="filiere.id" class="border-t">
                                        <td class="px-6 py-4">{{ filiere.name }}</td>
                                        <td class="px-6 py-4">{{ formatDate(filiere.created_at) }}</td>
                                        <td class="px-6 py-4">
                                            <button 
                                                @click="deleteFiliere(filiere.id)"
                                                class="text-red-600 hover:text-red-900">
                                                Supprimer
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
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    filieres: Array
})

const newFiliereName = ref('')

function addFiliere() {
    if (!newFiliereName.value.trim()) return
    
    router.post(route('admin.filieres.store'), {
        name: newFiliereName.value
    }, {
        onSuccess: () => {
            newFiliereName.value = ''
        }
    })
}

function deleteFiliere(id) {
    if (confirm('Supprimer cette filière ?')) {
        router.delete(route('admin.filieres.destroy', id))
    }
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR')
}
</script>