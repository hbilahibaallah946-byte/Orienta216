<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-4">Inscription en attente</h2>
                <p class="text-gray-600 mb-6">
                    Votre compte est en cours de validation par l'administrateur.<br>
                    Vous recevrez une notification dès que votre compte sera activé.
                </p>
                <Link :href="route('login')" class="text-blue-500 hover:text-blue-700">
                    Retour à la page de connexion
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>