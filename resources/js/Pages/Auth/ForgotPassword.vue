<script setup>
import InputError from '@/Components/InputError.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({ status: { type: String } })

const form = useForm({ email: '' })
const submit = () => form.post(route('password.email'))
</script>

<template>
  <Head title="Mot de passe oublié" />

  <div class="min-h-screen flex items-center justify-center bg-blue-950 p-4">
    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex" style="min-height: 480px;">

      <!-- LEFT PANEL -->
      <div class="relative w-2/5 bg-blue-700 flex flex-col items-center justify-center overflow-hidden p-10">

        <!-- Geometric blobs -->
        <div class="absolute top-0 left-0 w-56 h-56 bg-blue-500 opacity-40 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72 bg-blue-800 opacity-50 rounded-full translate-x-1/3 translate-y-1/3"></div>
        <div class="absolute top-1/2 left-1/2 w-48 h-48 bg-blue-400 opacity-20 rotate-45 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute top-1/4 right-0 w-32 h-32 bg-cyan-400 opacity-20 rounded-full translate-x-1/2"></div>

        <!-- Curved notch with label -->
        <div class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center">
          <div class="w-10 h-28 bg-white rounded-l-full flex items-center justify-start pl-1">
            <span class="text-blue-700 font-black text-[9px] tracking-widest uppercase rotate-90 whitespace-nowrap">Réinitialiser</span>
          </div>
        </div>

        <!-- Illustration placeholder — big emoji + rings -->
        <div class="relative z-10 text-center">
          <div class="relative w-36 h-36 mx-auto mb-6">
            <!-- Outer ring -->
            <div class="absolute inset-0 rounded-full border-4 border-white border-opacity-20"></div>
            <!-- Inner ring -->
            <div class="absolute inset-4 rounded-full border-2 border-white border-opacity-30 bg-white bg-opacity-10 backdrop-blur-sm flex items-center justify-center">
              <span class="text-5xl">🔑</span>
            </div>
            <!-- Floating question marks -->
            <span class="absolute -top-2 -right-1 text-2xl animate-bounce">❓</span>
            <span class="absolute -top-4 right-6 text-lg animate-bounce" style="animation-delay:.3s">❓</span>
          </div>

          <h2 class="text-white text-3xl font-extrabold tracking-wide mb-2">Orienta+216</h2>
          <p class="text-blue-200 text-sm font-medium tracking-widest uppercase">Récupère ton accès</p>

          <p class="text-blue-200 text-xs mt-4 max-w-xs leading-relaxed opacity-80">
            Pas de panique ! Saisis ton email et nous t'enverrons un lien de réinitialisation.
          </p>
        </div>
      </div>

      <!-- RIGHT PANEL -->
      <div class="flex-1 flex flex-col justify-center px-10 py-10">

        <h1 class="text-blue-700 text-2xl font-extrabold text-center mb-1 tracking-wide uppercase">
          Mot de passe oublié ?
        </h1>
        <p class="text-gray-400 text-xs text-center mb-8 max-w-xs mx-auto leading-relaxed">
          Indique ton adresse email, nous t'enverrons un lien pour réinitialiser ton mot de passe.
        </p>

        <!-- Status success -->
        <div v-if="status" class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">

          <!-- Email field -->
          <div class="relative">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <input
              type="email"
              v-model="form.email"
              placeholder="Ton adresse email"
              required
              autofocus
              class="w-full pl-12 pr-4 py-3 border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:outline-none text-gray-700 placeholder-gray-300 transition-colors bg-transparent text-sm"
            />
            <InputError class="text-xs mt-1" :message="form.errors.email" />
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold py-3 rounded-xl shadow-lg hover:shadow-blue-200 hover:shadow-xl transition-all transform hover:-translate-y-0.5 text-sm tracking-wide uppercase"
          >
            {{ form.processing ? 'Envoi en cours...' : 'Envoyer le lien' }}
          </button>

        </form>

        <p class="text-center text-xs text-gray-400 mt-6">
          Tu te souviens ?
          <Link :href="route('login')" class="text-blue-600 font-semibold hover:underline ml-1">
            Retour à la connexion
          </Link>
        </p>
      </div>

    </div>
  </div>
</template>

<style>
@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}
.animate-bounce { animation: bounce 1.4s ease-in-out infinite; }
</style>