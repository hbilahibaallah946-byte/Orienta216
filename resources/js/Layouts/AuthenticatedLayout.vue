<!-- resources/js/Layouts/AuthenticatedLayout.vue -->
<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
        <nav class="bg-white dark:bg-gray-800 shadow-md transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-gray-800 dark:text-white">Orienta</h1>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <!-- Menu Admin (Super Admin) -->
                            <template v-if="$page.props.auth.user.role === 'admin'">
                                <a :href="route('admin.dashboard')" class="nav-link dark:text-gray-300 dark:hover:text-white dark:hover:border-gray-500">
                                    Dashboard
                                </a>
                                <a :href="route('admin.pending-requests')" class="nav-link dark:text-gray-300 dark:hover:text-white dark:hover:border-gray-500 relative">
                                    Demandes en attente
                                    <span v-if="pendingCount > 0" 
                                          class="absolute -top-2 -right-6 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">
                                        {{ pendingCount }}
                                    </span>
                                </a>
                                <a :href="route('admin.users.index')" class="nav-link dark:text-gray-300 dark:hover:text-white dark:hover:border-gray-500">
                                    Utilisateurs
                                </a>
                                <a :href="route('admin.statistiques')" class="nav-link dark:text-gray-300 dark:hover:text-white dark:hover:border-gray-500">
                                    Statistiques
                                </a>
                            </template>
                            
                            <!-- Menu Conseiller (Dashboard supprimé) -->
                            <template v-else-if="$page.props.auth.user.role === 'conseiller'">
                                <a :href="route('conseiller.etudiants.index')" class="nav-link">
                                    Étudiants
                                </a>
                                <a :href="route('conseiller.filieres.index')" class="nav-link">
                                    Filières
                                </a>
                                <Link :href="route('conseiller.questionnaires.index')" class="nav-link">
                                    Questionnaires
                                </Link>
                                <a :href="route('conseiller.statistiques')" class="nav-link">
                                    Statistiques
                                </a>
                                <Link :href="route('conseiller.university-pdf.manage')" class="nav-link">
                                    Universités privées
                                </Link>
                            </template>
                            
                            <!-- Menu Étudiant -->
                            <template v-else>
                                <Link :href="route('etudiant.dashboard')" class="nav-link">
                                    Mon Dashboard
                                </Link>
                                <Link :href="route('etudiant.questionnaires.index')" class="nav-link">
                                    Mes Questionnaires
                                </Link>
                                <Link :href="route('moyennes.index')" class="nav-link">
                                    Mes Moyennes
                                </Link>
                                <Link :href="route('etudiant.private-universities')" class="nav-link">
                                    Universités Privées
                                </Link>
                            </template>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-3">
                            <span class="text-gray-700 dark:text-gray-300">
                                {{ $page.props.auth.user.name }}
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                                    ({{ getRoleLabel($page.props.auth.user.role) }})
                                </span>
                            </span>
                            
                            <button 
                                @click="toggleDarkMode" 
                                class="relative inline-flex items-center justify-center w-10 h-10 rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
                                :class="isDarkMode ? 'bg-gray-700 text-yellow-400 hover:bg-gray-600' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                :aria-label="isDarkMode ? 'Activer le mode clair' : 'Activer le mode nuit'"
                            >
                                <svg v-if="isDarkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <button @click="logout" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                            Déconnexion
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>

        <!-- Chat visible sur toutes les pages -->
        <ChatIcon />
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { router, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted } from 'vue'
import ChatIcon from '@/Components/ChatIcon.vue'   // <-- ajout

const page = usePage()

const pendingCount = computed(() => page.props.pendingCount || 0)

// État du mode sombre
const isDarkMode = ref(false)

// Fonction pour basculer le mode
const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value
    applyDarkMode()
}

// Appliquer le mode
const applyDarkMode = () => {
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
    } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
    }
}

// Initialiser le thème
const initTheme = () => {
    const savedTheme = localStorage.getItem('theme')
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    
    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        isDarkMode.value = true
        applyDarkMode()
    }
}

function getRoleLabel(role) {
    const roles = {
        admin: 'Admin',
        conseiller: 'Conseiller',
        etudiant: 'Étudiant'
    }
    return roles[role] || role
}

function logout() {
    router.post('/logout')
}

onMounted(() => {
    initTheme()
    
    // Écouter les changements de préférence système
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            isDarkMode.value = e.matches
            applyDarkMode()
        }
    })
})
</script>

<style scoped>
.nav-link {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-bottom-width: 2px;
    border-bottom-color: transparent;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    text-decoration: none;
    transition: all 0.3s ease;
}

.nav-link:hover {
    color: #374151;
    border-bottom-color: #d1d5db;
}

/* Animation du bouton */
button:active {
    transform: scale(0.95);
}
</style>