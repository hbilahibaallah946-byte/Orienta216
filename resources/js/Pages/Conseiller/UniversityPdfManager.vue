<template>
  <AuthenticatedLayout>
    <div class="py-6 max-w-4xl mx-auto">
      <!-- Message flash -->
      <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-xl">
        {{ $page.props.flash.success }}
      </div>

      <!-- PDF actuel -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-6 mb-6">
        <h2 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Document actuel</h2>
        <div v-if="pdf" class="space-y-2">
          <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Fichier :</span> {{ pdf.filename }}</p>
          <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Taille :</span> {{ pdf.size }}</p>
          <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Dernière mise à jour :</span> {{ pdf.updated_at }}</p>
          <div class="mt-4 flex space-x-3">
            <a :href="pdf.url" target="_blank" class="px-6 py-2 bg-indigo-700 text-white rounded-full shadow-sm hover:bg-indigo-800 transition-colors">
              Voir le PDF
            </a>
            <button @click="deletePdf" class="px-6 py-2 border-2 border-red-600 text-red-700 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" :disabled="formDelete.processing">
              Supprimer
            </button>
          </div>
        </div>
        <p v-else class="text-gray-500 dark:text-gray-400 italic">Aucun PDF stocké.</p>
      </div>

      <!-- Formulaire d'upload -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-6">
        <h2 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Remplacer par un nouveau PDF</h2>
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block font-medium mb-1 text-gray-700 dark:text-gray-300">Fichier PDF (max 20 Mo)</label>
            <input type="file" accept="application/pdf" @change="handleFile" class="w-full border p-2 rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-800 hover:file:bg-indigo-200" required />
            <div v-if="form.errors.pdf_file" class="text-red-500 text-sm mt-1">{{ form.errors.pdf_file }}</div>
          </div>
          <button type="submit" class="px-5 py-2 bg-emerald-700 text-white rounded-full shadow-sm hover:bg-emerald-800 transition-colors disabled:opacity-50" :disabled="form.processing || !form.pdf_file">
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