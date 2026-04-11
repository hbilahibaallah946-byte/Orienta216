<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Créer un nouvel étudiant</h1>
                        
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Nom complet</label>
                                <input 
                                    type="text" 
                                    v-model="form.name" 
                                    class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400"
                                    required
                                >
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.name }}
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input 
                                    type="email" 
                                    v-model="form.email" 
                                    class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400"
                                    required
                                >
                                <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.email }}
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Mot de passe</label>
                                <input 
                                    type="password" 
                                    v-model="form.password" 
                                    class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400"
                                    required
                                >
                                <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.password }}
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Filière</label>
                                <select v-model="form.filiere_id" class="w-full border rounded-lg p-2">
                                    <option value="">Sélectionner une filière</option>
                                    <option v-for="filiere in filieres" :key="filiere.id" :value="filiere.id">
                                        {{ filiere.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="flex justify-end space-x-4">
                                <Link :href="route('conseiller.etudiants.index')" 
                                      class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                    Annuler
                                </Link>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Création...' : 'Créer l\'étudiant' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    filieres: Array
})

const form = useForm({
    name: '',
    email: '',
    password: '',
    filiere_id: ''
})

function submit() {
    form.post(route('conseiller.etudiants.store'))
}
</script>