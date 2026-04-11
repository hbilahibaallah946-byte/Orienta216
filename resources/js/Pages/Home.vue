<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-600 to-purple-700 text-white">

    <div class="container mx-auto px-6 py-24 text-center">
      <h1 class="text-5xl font-extrabold mb-6">Orienta 🎓</h1>

      <p class="text-xl mb-10 max-w-2xl mx-auto">
        Plateforme intelligente d’orientation universitaire pour les bacheliers tunisiens.
      </p>

      <div class="space-x-4">
        <!-- Commencer => Register -->
        <Link href="/register"
          class="bg-white text-indigo-700 px-8 py-3 rounded-xl font-semibold hover:bg-indigo-100 transition">
          Commencer
        </Link>

        <!-- Connexion => Login -->
        <Link href="/login"
          class="border border-white px-8 py-3 rounded-xl font-semibold hover:bg-white hover:text-indigo-700 transition">
          Connexion
        </Link>
      </div>
    </div>

  </div>
</template>
