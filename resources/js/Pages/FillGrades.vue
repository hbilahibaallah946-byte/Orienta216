<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Remplir vos notes</h1>

    <div v-if="subjects && subjects.length">
      <div v-for="sub in subjects" :key="sub" class="mb-2">
        <label class="block font-semibold">{{ sub }}</label>
        <input type="number" class="border p-2 rounded w-full" />
      </div>
    </div>

    <p v-else>Aucune matière trouvée.</p>
  </div>
</template>

<script setup>
defineProps({
  subjects: Array,
  speciality: String
});
</script>
