<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Choisissez votre spécialité</h1>

    <form @submit.prevent="submit">
      <label class="block mb-2 font-semibold">Spécialité :</label>

      <select v-model="form.speciality" class="border p-2 rounded w-full mb-4">
        <option value="" disabled>-- Choisir une spécialité --</option>
        <option v-for="spec in specialities" :key="spec" :value="spec">
          {{ spec }}
        </option>
      </select>

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
        Suivant
      </button>
    </form>
  </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
  specialities: Array
});

const form = useForm({
  speciality: ""
});

function submit() {
  form.post(route("store-speciality"));
}
</script>
