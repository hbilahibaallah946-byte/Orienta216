<template>
  <AuthenticatedLayout>
    <div class="py-6 max-w-4xl mx-auto">
      <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">
        Gestion du PDF des universités privées
      </h1>

      <!-- Message flash -->
      <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded">
        {{ $page.props.flash.success }}
      </div>

      <!-- PDF actuel -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Document actuel</h2>
        <div v-if="pdf" class="space-y-2">
          <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Fichier :</span> {{ pdf.filename }}</p>
          <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Taille :</span> {{ pdf.size }}</p>
          <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Dernière mise à jour :</span> {{ pdf.updated_at }}</p>
          <div class="mt-4 flex space-x-3">
            <a :href="pdf.url" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
              Voir le PDF
            </a>
            <button @click="deletePdf" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" :disabled="formDelete.processing">
              Supprimer
            </button>
          </div>
        </div>
        <p v-else class="text-gray-500 dark:text-gray-400 italic">Aucun PDF stocké.</p>
      </div>

      <!-- Formulaire d'upload -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Remplacer par un nouveau PDF</h2>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block font-medium mb-1 text-gray-700 dark:text-gray-300">Fichier PDF (max 20 Mo)</label>
            <input type="file" accept="application/pdf" @change="handleFile" class="w-full border p-2 rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" required />
            <div v-if="form.errors.pdf_file" class="text-red-500 text-sm mt-1">{{ form.errors.pdf_file }}</div>
          </div>
          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700" :disabled="form.processing || !form.pdf_file">
            {{ form.processing ? 'Envoi...' : 'Mettre à jour le PDF' }}
          </button>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  pdf: Object,
});

const form = useForm({
  pdf_file: null,
});

const formDelete = useForm({});

function handleFile(e) {
  form.pdf_file = e.target.files[0];
}

function submit() {
  form.post(route('conseiller.university-pdf.upload'), {
    onSuccess: () => form.reset(),
  });
}

function deletePdf() {
  if (confirm('Supprimer définitivement le PDF ?')) {
    formDelete.delete(route('conseiller.university-pdf.delete'));
  }
}
</script>