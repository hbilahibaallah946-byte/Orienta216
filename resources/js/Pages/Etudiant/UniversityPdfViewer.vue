<template>
  <AuthenticatedLayout title="Universités Privées Reconnues">
    <div class="py-6 max-w-6xl mx-auto">
      <h1 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
        Liste officielle des universités privées
      </h1>

      <div v-if="pdf" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="bg-gray-100 dark:bg-gray-700 px-4 py-2 border-b dark:border-gray-600 flex justify-between items-center">
          <span class="text-sm text-gray-600 dark:text-gray-300">
            {{ pdf.filename }} (mis à jour le {{ pdf.updated_at }})
          </span>
          <a :href="pdf.url" download class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
            Télécharger
          </a>
        </div>
        <embed :src="pdf.url" type="application/pdf" class="w-full" style="height: 80vh;" />
      </div>

      <div v-else class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4 rounded text-center">
        <p class="text-yellow-800 dark:text-yellow-300">
          Aucun document PDF n'est disponible pour le moment.
        </p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
  pdf: Object,
});
</script>