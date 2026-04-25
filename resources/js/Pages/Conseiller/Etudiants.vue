<template>
  <AuthenticatedLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 py-10 transition-colors">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors">
          <div class="p-6 sm:p-8">
            <!-- Titre -->
            <div class="flex items-center gap-3 mb-8">
              <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Liste des étudiants</h1>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-xl">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                  <tr class="bg-gray-50 dark:bg-gray-700/50">
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Inscription</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                  <tr
                    v-for="etudiant in etudiants"
                    :key="etudiant.id"
                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                  >
                    <td class="px-6 py-4 whitespace-nowrap">
                      <Link
                        :href="route('conseiller.etudiants.show', etudiant.id)"
                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium underline-offset-2 hover:underline transition"
                      >
                        {{ etudiant.name }}
                      </Link>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                      {{ etudiant.email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                      {{ new Date(etudiant.created_at).toLocaleDateString('fr-FR') }}
                    </td>
                  </tr>
                  <tr v-if="!etudiants || etudiants.length === 0">
                    <td colspan="3" class="px-6 py-12 text-center">
                      <div class="flex flex-col items-center text-gray-400 dark:text-gray-500">
                        <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <p class="text-lg font-medium">Aucun étudiant trouvé</p>
                      </div>
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
  etudiants: Array,
})
</script>