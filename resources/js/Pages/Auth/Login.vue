<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

function login() {
  form.post(route('login'))
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-blue-950 p-4">
    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex" style="min-height: 520px;">

      <!-- LEFT PANEL -->
      <div class="relative w-2/5 bg-blue-700 flex flex-col items-center justify-center overflow-hidden p-10">

        <!-- Geometric shapes -->
        <div class="absolute top-0 left-0 w-56 h-56 bg-blue-500 opacity-40 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72 bg-blue-800 opacity-50 rounded-full translate-x-1/3 translate-y-1/3"></div>
        <div class="absolute top-1/2 left-1/2 w-48 h-48 bg-blue-400 opacity-20 rotate-45 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute top-1/4 right-0 w-32 h-32 bg-cyan-400 opacity-20 rounded-full translate-x-1/2"></div>

        <!-- Curved notch on right edge -->
        <div class="absolute right-0 top-1/2 -translate-y-1/2 w-10 h-24 bg-white rounded-l-full"></div>

        <!-- Content -->
        <div class="relative z-10 text-center">
          <div class="w-20 h-20 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center mx-auto mb-6 backdrop-blur-sm border border-white border-opacity-30">
            <span class="text-4xl">🎓</span>
          </div>
          <h2 class="text-white text-3xl font-extrabold tracking-wide mb-2">Orienta+216</h2>
          <p class="text-blue-200 text-sm font-medium tracking-widest uppercase">Ton avenir commence ici</p>
        </div>

        <!-- Floating badge -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 bg-white bg-opacity-15 backdrop-blur-sm border border-white border-opacity-20 text-white text-xs px-4 py-2 rounded-full whitespace-nowrap">
          ✨ Orientation intelligente
        </div>
      </div>

      <!-- RIGHT PANEL -->
      <div class="flex-1 flex flex-col justify-center px-10 py-10">

        <!-- Icon circle -->
        <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-2">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>

        <h1 class="text-blue-700 text-2xl font-extrabold text-center mb-1 tracking-wide uppercase">Connexion</h1>
        <p class="text-gray-400 text-sm text-center mb-7">Accède à ton espace personnel</p>

        <form @submit.prevent="login" class="space-y-4">

          <!-- Email -->
          <div class="relative">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
              </svg>
            </div>
            <input
              type="email"
              v-model="form.email"
              placeholder="Email"
              class="w-full pl-12 pr-4 py-3 border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:outline-none text-gray-700 placeholder-gray-300 transition-colors bg-transparent text-sm"
            />
            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
          </div>

          <!-- Password -->
          <div class="relative">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              placeholder="Mot de passe"
              class="w-full pl-12 pr-12 py-3 border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:outline-none text-gray-700 placeholder-gray-300 transition-colors bg-transparent text-sm"
            />
            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-blue-500 transition-colors">
              <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
              </svg>
            </button>
            <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
          </div>

          <!-- Remember + Forgot -->
          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer">
              <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded accent-blue-600"/>
              Se souvenir de moi
            </label>
            <Link :href="route('password.request')" class="text-xs text-blue-500 hover:text-blue-700 transition font-medium">
              Mot de passe oublié ?
            </Link>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold py-3 rounded-xl shadow-lg hover:shadow-blue-200 hover:shadow-xl transition-all transform hover:-translate-y-0.5 mt-2 text-sm tracking-wide uppercase"
          >
            {{ form.processing ? 'Connexion...' : 'Se connecter' }}
          </button>

        </form>

        <p class="text-center text-xs text-gray-400 mt-5">
          Pas encore de compte ?
          <Link :href="route('register')" class="text-blue-600 font-semibold hover:underline ml-1">S'inscrire</Link>
        </p>
      </div>
    </div>
  </div>
</template>