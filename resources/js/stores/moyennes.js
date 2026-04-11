// resources/js/stores/moyennes.js
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useMoyennesStore = defineStore('moyennes', () => {
    const moyennes = ref([])
    const loading = ref(false)
    const error = ref(null)
    
    async function fetchMoyennes() {
        loading.value = true
        error.value = null
        
        try {
            const response = await fetch('/moyennes', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin' // Important pour les cookies de session
            })
            
            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.message || 'Erreur lors du chargement des moyennes')
            }
            
            const data = await response.json()
            moyennes.value = data
            return data
        } catch (err) {
            error.value = err.message
            console.error('Erreur fetchMoyennes:', err)
            return []
        } finally {
            loading.value = false
        }
    }
    
    async function addMoyenne(moyenneData) {
        loading.value = true
        error.value = null
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
            
            console.log('Envoi des données:', moyenneData) // Pour déboguer
            
            const response = await fetch('/moyennes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin', // Important pour les cookies
                body: JSON.stringify(moyenneData)
            })
            
            if (!response.ok) {
                const errorData = await response.json()
                console.error('Erreur réponse:', errorData)
                throw new Error(errorData.message || 'Erreur lors de l\'enregistrement')
            }
            
            const result = await response.json()
            console.log('Résultat:', result)
            
            // Recharger les moyennes après ajout
            await fetchMoyennes()
            return true
        } catch (err) {
            error.value = err.message
            console.error('Erreur addMoyenne:', err)
            return false
        } finally {
            loading.value = false
        }
    }
    
    return { 
        moyennes, 
        loading, 
        error, 
        fetchMoyennes, 
        addMoyenne 
    }
})