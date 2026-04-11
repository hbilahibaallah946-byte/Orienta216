<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-6">Créer un nouvel utilisateur</h1>
                        
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Nom complet</label>
                                <input 
                                    type="text" 
                                    v-model="form.name" 
                                    class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
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
                                    class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
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
                                    class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                    required
                                >
                                <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.password }}
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Rôle</label>
                                <select v-model="form.role" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="admin">Admin</option>
                                    <option value="conseiller">Conseiller</option>
                                    <option value="etudiant">Étudiant</option>
                                </select>
                                <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.role }}
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-4">
                                <a :href="route('admin.users.index')" 
                                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition">
                                    Annuler
                                </a>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50 transition">
                                    {{ form.processing ? 'Création...' : 'Créer l\'utilisateur' }}
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
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: ''
})

function submit() {
    form.post(route('admin.users.store'))
}
</script>