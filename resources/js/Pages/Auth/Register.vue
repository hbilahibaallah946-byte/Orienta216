<script setup>
import confetti from 'canvas-confetti'
import InputError from '@/Components/InputError.vue'
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const page = usePage()
const showModal = ref(false)
const userName = ref('')
const showPassword = ref(false)
const showConfirm = ref(false)

watch(() => page.props.flash?.success, (message) => {
  if (message) {
    userName.value = message
    showModal.value = true

    const duration = 3 * 1000
    const end = Date.now() + duration
    const frame = () => {
      confetti({ particleCount: 5, angle: 60, spread: 55, origin: { x: 0 } })
      confetti({ particleCount: 5, angle: 120, spread: 55, origin: { x: 1 } })
      if (Date.now() < end) requestAnimationFrame(frame)
    }
    frame()
  }
})

const goToLogin = () => router.visit(route('login'))

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'etudiant',
})

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Inscription" />

  <div class="min-h-screen flex items-center justify-center bg-blue-950 p-4">
    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex" style="min-height: 580px;">

      <!-- LEFT PANEL -->
      <div class="relative w-2/5 bg-blue-700 flex flex-col items-center justify-center overflow-hidden p-10">

        <div class="absolute top-0 left-0 w-56 h-56 bg-blue-500 opacity-40 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72 bg-blue-800 opacity-50 rounded-full translate-x-1/3 translate-y-1/3"></div>
        <div class="absolute top-1/2 left-1/2 w-48 h-48 bg-blue-400 opacity-20 rotate-45 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute top-1/4 right-0 w-32 h-32 bg-cyan-400 opacity-20 rounded-full translate-x-1/2"></div>

        <!-- Curved notch -->
        <div class="absolute right-0 top-1/2 -translate-y-1/2 w-10 h-24 bg-white rounded-l-full"></div>

        <div class="relative z-10 text-center">
          <div class="w-20 h-20 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center mx-auto mb-6 backdrop-blur-sm border border-white border-opacity-30">
            <span class="text-4xl">🎓</span>
          </div>
          <h2 class="text-white text-3xl font-extrabold tracking-wide mb-2">Orienta+216</h2>
          <p class="text-blue-200 text-sm font-medium tracking-widest uppercase">Rejoins-nous aujourd'hui</p>

          <div class="mt-8 space-y-3">
            <div class="flex items-center gap-3 text-blue-100 text-sm">
              <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center text-base">🎯</div>
              <span>Test d'orientation intelligent</span>
            </div>
            <div class="flex items-center gap-3 text-blue-100 text-sm">
              <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center text-base">🏫</div>
              <span>Comparaison d'universités</span>
            </div>
            <div class="flex items-center gap-3 text-blue-100 text-sm">
              <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center text-base">📊</div>
              <span>Recommandations personnalisées</span>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT PANEL -->
      <div class="flex-1 flex flex-col justify-center px-10 py-8">

        <h1 class="text-blue-700 text-2xl font-extrabold text-center mb-1 tracking-wide uppercase">Inscription</h1>
        <p class="text-gray-400 text-xs text-center mb-6">Crée ton compte et commence ton orientation</p>

        <form @submit.prevent="submit" class="space-y-4">

          <!-- Name -->
          <div class="relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <input
              type="text"
              v-model="form.name"
              placeholder="Nom complet"
              required
              autofocus
              class="w-full pl-10 pr-4 py-3 border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:outline-none text-gray-700 placeholder-gray-300 transition-colors bg-transparent text-sm"
            />
            <InputError class="text-xs mt-1" :message="form.errors.name" />
          </div>

          <!-- Email -->
          <div class="relative">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
              </svg>
            </div>
            <input
              type="email"
              v-model="form.email"
              placeholder="Email"
              required
              class="w-full pl-10 pr-4 py-3 border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:outline-none text-gray-700 placeholder-gray-300 transition-colors bg-transparent text-sm"
            />
            <InputError class="text-xs mt-1" :message="form.errors.email" />
          </div>

          <!-- Password row -->
          <div class="grid grid-cols-2 gap-4">

            <div class="relative">
              <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                placeholder="Mot de passe"
                required
                class="w-full pl-9 pr-8 py-3 border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:outline-none text-gray-700 placeholder-gray-300 transition-colors bg-transparent text-sm"
              />
              <button type="button" @click="showPassword = !showPassword" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300 hover:text-blue-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"/>
                </svg>
              </button>
              <InputError class="text-xs mt-1" :message="form.errors.password" />
            </div>

            <div class="relative">
              <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
              </div>
              <input
                :type="showConfirm ? 'text' : 'password'"
                v-model="form.password_confirmation"
                placeholder="Confirmer"
                required
                class="w-full pl-9 pr-8 py-3 border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:outline-none text-gray-700 placeholder-gray-300 transition-colors bg-transparent text-sm"
              />
              <button type="button" @click="showConfirm = !showConfirm" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300 hover:text-blue-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="!showConfirm" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"/>
                </svg>
              </button>
              <InputError class="text-xs mt-1" :message="form.errors.password_confirmation" />
            </div>

          </div>

          <!-- Role selector - pill style -->
          <div>
            <p class="text-xs text-gray-500 mb-2 font-medium">Je m'inscris en tant que :</p>
            <div class="flex gap-3">
              <button
                type="button"
                @click="form.role = 'etudiant'"
                :class="form.role === 'etudiant'
                  ? 'bg-blue-600 text-white shadow-lg shadow-blue-200'
                  : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2"
              >
                <span>🎓</span> Étudiant
              </button>
              <button
                type="button"
                @click="form.role = 'conseiller'"
                :class="form.role === 'conseiller'
                  ? 'bg-blue-600 text-white shadow-lg shadow-blue-200'
                  : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2"
              >
                <span>👨‍💼</span> Conseiller
              </button>
            </div>
            <InputError class="text-xs mt-1" :message="form.errors.role" />
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold py-3 rounded-xl shadow-lg hover:shadow-blue-200 hover:shadow-xl transition-all transform hover:-translate-y-0.5 text-sm tracking-wide uppercase"
          >
            {{ form.processing ? 'Inscription...' : "S'inscrire" }}
          </button>

        </form>

        <p class="text-center text-xs text-gray-400 mt-4">
          Déjà un compte ?
          <Link :href="route('login')" class="text-blue-600 font-semibold hover:underline ml-1">Se connecter</Link>
        </p>
      </div>
    </div>
  </div>

  <!-- Welcome Modal -->
  <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm z-50">
    <div class="bg-white rounded-3xl shadow-2xl p-12 text-center w-[480px] relative animate-fadeIn">
      <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <span class="text-4xl">🎉</span>
      </div>
      <h2 class="text-2xl font-extrabold text-gray-800 mb-2">Bienvenue {{ userName }} !</h2>
      <p class="text-gray-400 mb-8 text-sm">Votre compte est en cours de validation par l'administrateur.</p>
      <button
        @click="goToLogin"
        class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-3 rounded-xl shadow-lg text-sm font-bold uppercase tracking-wide transition transform hover:-translate-y-0.5"
      >
        Se connecter
      </button>
    </div>
  </div>
</template>

<style>
.animate-fadeIn {
  animation: fadeIn 0.4s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.9); }
  to   { opacity: 1; transform: scale(1); }
}
</style>F