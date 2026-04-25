<template>
  <div class="fixed inset-0 bg-white dark:bg-gray-900">
    <!-- Icône retour (flèche) en haut à gauche -->
    <button
      v-if="pdf"
      @click="goBack"
      class="absolute top-4 left-4 z-10 p-1.5 rounded-full bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm hover:bg-white/90 dark:hover:bg-gray-700/90 transition-colors text-gray-700 dark:text-gray-300 shadow-sm"
      title="Retour"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
    </button>

  

    <!-- PDF plein écran -->
    <embed
      v-if="pdf"
      :src="pdf.url"
      type="application/pdf"
      class="absolute inset-0 w-full h-full"
    />

    <!-- Message si aucun PDF -->
    <div v-if="!pdf" class="flex items-center justify-center h-full">
      <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 p-4 rounded text-center">
        <p class="text-yellow-800 dark:text-yellow-300">
          Aucun document PDF n'est disponible pour le moment.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  pdf: Object,
})

const goBack = () => {
  window.history.back()
}
</script>