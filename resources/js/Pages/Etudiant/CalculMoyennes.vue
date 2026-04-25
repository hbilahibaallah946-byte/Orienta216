<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useMoyennesStore } from '@/stores/moyennes'

const props = defineProps({
  language: { type: String, required: true },
  theme: { type: String, required: true },
  t: { type: Function, required: true }
})

const emit = defineEmits(['done'])

const moyennesStore = useMoyennesStore()

const step = ref('specialites')
const selectedSpecialite = ref(null)
const showDispenseSport = ref(false)
const isSubmitting = ref(false)

// Navigation entre sessions
const sessionActive = ref('principale')
const sectionPrincipale = ref(null)
const sectionControle = ref(null)

const matieres = ref([])

// Modal
const showResultModal = ref(false)
const resultData = ref({
  type: '',
  moyenne: '0.00',
  score: '0.00',
  scorePlus7: '0.00',
  passable: false
})

// Mention calculée
const mentionResult = computed(() => {
  const moy = parseFloat(resultData.value.moyenne)
  if (isNaN(moy)) return ''
  if (moy < 7) return props.t('refuse')
  if (moy < 10) return props.t('controle')
  if (moy < 12) return props.t('passable')
  if (moy < 14) return props.t('assez_bien')
  if (moy < 16) return props.t('bien')
  return props.t('tres_bien')
})

// ----- Spécialités (icônes identiques) -----
const specialites = [
  { id: 'sciences-experimentales', nom: 'Sciences expérimentales', color: 'from-emerald-400 to-teal-500',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>` },
  { id: 'mathématique', nom: 'Mathématiques', color: 'from-blue-400 to-indigo-500',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v2h16V7H4zm0 4v2h4v-2H4zm6 0v2h10v-2H10zM4 15v2h16v-2H4zm0 4v2h16v-2H4z" /></svg>` },
  { id: 'sport', nom: 'Sport', color: 'from-orange-400 to-red-500',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l-4-4m0 0l-4 4m4-4v12m5 4H8a2 2 0 01-2-2v-4a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2z" /></svg>` },
  { id: 'lettres', nom: 'Lettres', color: 'from-purple-400 to-pink-500',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>` },
  { id: 'économie-gestion', nom: 'Économie & Gestion', color: 'from-yellow-400 to-amber-500',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>` },
  { id: 'techniques', nom: 'Techniques', color: 'from-gray-500 to-slate-600',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>` },
  { id: 'informatique', nom: 'Informatique', color: 'from-cyan-400 to-blue-500',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>` }
]

// ----- Matières par spécialité -----
const matieresParSpecialite = {
  'sciences-experimentales': [
    { nom: 'Mathématiques', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Physique-Chimie', coefficient: 4, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'SVT', coefficient: 4, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Arabe', coefficient: 2, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Philosophie', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Français', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Anglais', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Sport', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false, hasDispense: false },
    { nom: 'Informatique', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Option', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false }
  ],
  'mathématique': [
    { nom: 'Mathématiques', coefficient: 4, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Physique-Chimie', coefficient: 4, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'SVT', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Arabe', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Philosophie', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Français', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Anglais', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Sport', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false, hasDispense: false },
    { nom: 'Informatique', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Option', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false }
  ],
  'sport': [
    { nom: 'Sciences Biol.', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Sport Pratique', coefficient: 2.5, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Sciences Physique', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Education Physique', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Sport Théorique', coefficient: 0.5, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Arabe', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Français', coefficient: 1.5, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Anglais', coefficient: 1.5, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Philosophie', coefficient: 1.5, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Mathématiques', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Option', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false }
  ],
  'lettres': [
    { nom: 'Arabe', coefficient: 4, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Français', coefficient: 2, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Anglais', coefficient: 2, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Philosophie', coefficient: 4, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Histoire-Géographie', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Pensée Islamique', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Sport', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false, hasDispense: false },
    { nom: 'Informatique', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Option', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false }
  ],
  'économie-gestion': [
    { nom: 'Économie', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Gestion', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Histoire & Géographie', coefficient: 2, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Mathématiques', coefficient: 2, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Arabe', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Français', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Anglais', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Philosophie', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Sport', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false, hasDispense: false },
    { nom: 'Informatique', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Option', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false }
  ],
  'techniques': [
    { nom: 'Mathématiques', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Physique', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Technologie', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'TP Technologie', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Arabe', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Français', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Philosophie', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Anglais', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Sport', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false, hasDispense: false },
    { nom: 'Informatique', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Option', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false }
  ],
  'informatique': [
    { nom: 'Mathématiques', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Option', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'STI', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Sciences Physiques', coefficient: 2, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Algo & Prog', coefficient: 3, notePrincipale: '', noteControle: '', repasser: false, isMain: true },
    { nom: 'Arabe', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Français', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Anglais', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Philosophie', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false },
    { nom: 'Sport', coefficient: 1, notePrincipale: '', noteControle: '', repasser: false, isMain: false, hasDispense: false }
  ]
}

// ----- Formules de calcul du score d'orientation (4*MG + coefficients matières principales) -----
const scoreFormulas = {
  'lettres': [
    { nom: 'Arabe', coef: 1.5 },
    { nom: 'Philosophie', coef: 1.5 },
    { nom: 'Histoire-Géographie', coef: 1 },
    { nom: 'Français', coef: 1 },
    { nom: 'Anglais', coef: 1 }
  ],
  'mathématique': [
    { nom: 'Mathématiques', coef: 2 },
    { nom: 'Physique-Chimie', coef: 1.5 },
    { nom: 'SVT', coef: 0.5 },
    { nom: 'Français', coef: 1 },
    { nom: 'Anglais', coef: 1 }
  ],
  'sciences-experimentales': [
    { nom: 'Mathématiques', coef: 1 },
    { nom: 'Physique-Chimie', coef: 1.5 },
    { nom: 'SVT', coef: 1.5 },
    { nom: 'Français', coef: 1 },
    { nom: 'Anglais', coef: 1 }
  ],
  'économie-gestion': [
    { nom: 'Économie', coef: 1.5 },
    { nom: 'Gestion', coef: 1.5 },
    { nom: 'Mathématiques', coef: 0.5 },
    { nom: 'Histoire & Géographie', coef: 0.5 },
    { nom: 'Français', coef: 1 },
    { nom: 'Anglais', coef: 1 }
  ],
  'techniques': [
    { nom: 'Technologie', coef: 1.5 },
    { nom: 'Mathématiques', coef: 1.5 },
    { nom: 'Physique', coef: 1 },
    { nom: 'Français', coef: 1 },
    { nom: 'Anglais', coef: 1 }
  ],
  'informatique': [
    { nom: 'Mathématiques', coef: 1.5 },
    { nom: 'Algo & Prog', coef: 1.5 },
    { nom: 'Sciences Physiques', coef: 0.5 },
    { nom: 'STI', coef: 0.5 },
    { nom: 'Français', coef: 1 },
    { nom: 'Anglais', coef: 1 }
  ],
  'sport': [
    { nom: 'Sciences Biol.', coef: 1.5 },
    { nom: 'Sport Pratique', coef: 1 },
    { nom: 'Education Physique', coef: 0.5 },
    { nom: 'Sciences Physique', coef: 0.5 },
    { nom: 'Philosophie', coef: 0.5 },
    { nom: 'Français', coef: 1 },
    { nom: 'Anglais', coef: 1 }
  ]
}

// ----- Calculs -----
function notePrincipaleEffective(m) {
  // Sport dispensé = note ignorée (retourne null pour être sauté)
  if (m.hasDispense && m.nom === 'Sport') return null
  const n = parseFloat(m.notePrincipale)
  return isNaN(n) ? null : n
}

function noteEffectiveControle(m) {
  if (m.hasDispense && m.nom === 'Sport') return null
  const np = parseFloat(m.notePrincipale)
  if (isNaN(np)) return null
  if (m.repasser) {
    const nc = parseFloat(m.noteControle)
    if (!isNaN(nc)) return Math.max(np, nc)
  }
  return np
}

// Moyenne principale (sport dispensé exclu)
const moyennePrincipale = computed(() => {
  let total = 0, coefs = 0
  matieres.value.forEach(m => {
    if (m.hasDispense && m.nom === 'Sport') return // exclu
    const note = notePrincipaleEffective(m)
    if (note !== null) {
      total += note * m.coefficient
      coefs += m.coefficient
    }
  })
  return coefs ? (total / coefs).toFixed(2) : '0.00'
})

// Score d'orientation (principal)
const currentFormula = computed(() => {
  return selectedSpecialite.value ? scoreFormulas[selectedSpecialite.value.id] || [] : []
})

const scorePrincipale = computed(() => {
  const mg = parseFloat(moyennePrincipale.value)
  if (isNaN(mg)) return '0.00'
  let s = 4 * mg
  currentFormula.value.forEach(item => {
    const matiere = matieres.value.find(m => m.nom === item.nom)
    if (matiere) {
      const note = notePrincipaleEffective(matiere)
      if (note !== null) s += item.coef * note
    }
  })
  return s.toFixed(2)
})
const scorePlus7Principale = computed(() => (parseFloat(scorePrincipale.value) * 1.07).toFixed(2))
const passablePrincipale = computed(() => parseFloat(moyennePrincipale.value) >= 10)

// Remplissage session principale (sport dispensé = toujours rempli)
const allNotesPrincipaleFilled = computed(() => {
  return matieres.value.every(m => {
    if (m.hasDispense && m.nom === 'Sport') return true
    return m.notePrincipale !== '' && !isNaN(parseFloat(m.notePrincipale))
  })
})

// Moyenne contrôle (sport dispensé exclu)
const moyenneControle = computed(() => {
  let total = 0, coefs = 0
  matieres.value.forEach(m => {
    if (m.hasDispense && m.nom === 'Sport') return
    const note = noteEffectiveControle(m)
    if (note !== null) {
      total += note * m.coefficient
      coefs += m.coefficient
    }
  })
  return coefs ? (total / coefs).toFixed(2) : '0.00'
})

// Score d'orientation (contrôle)
const scoreControle = computed(() => {
  const mg = parseFloat(moyenneControle.value)
  if (isNaN(mg)) return '0.00'
  let s = 4 * mg
  currentFormula.value.forEach(item => {
    const matiere = matieres.value.find(m => m.nom === item.nom)
    if (matiere) {
      const note = noteEffectiveControle(matiere)
      if (note !== null) s += item.coef * note
    }
  })
  return s.toFixed(2)
})
const scorePlus7Controle = computed(() => (parseFloat(scoreControle.value) * 1.07).toFixed(2))
const passableControle = computed(() => parseFloat(moyenneControle.value) >= 10)

// Validation pour le calcul contrôle
const controleValid = computed(() => {
  if (!matieres.value.some(m => m.repasser)) return false
  return matieres.value.every(m => {
    if (!m.repasser) return true
    const noteP = notePrincipaleEffective(m)
    const noteC = parseFloat(m.noteControle)
    return noteP !== null && !isNaN(noteC) && m.noteControle !== ''
  })
})

// ----- Méthodes -----
function selectSpecialite(specialite) {
  selectedSpecialite.value = specialite
  matieres.value = JSON.parse(JSON.stringify(matieresParSpecialite[specialite.id]))
  step.value = 'matieres'
  sessionActive.value = 'principale'
  showDispenseSport.value = false
  nextTick(() => scrollToPrincipale())
}

function retourSpecialites() {
  step.value = 'specialites'
  selectedSpecialite.value = null
  matieres.value = []
}

function toggleDispenseSport() {
  const sport = matieres.value.find(m => m.nom === 'Sport')
  if (sport) {
    sport.hasDispense = !sport.hasDispense
    if (sport.hasDispense) {
      sport.notePrincipale = ''
      sport.noteControle = ''
      sport.repasser = false
    }
  }
}

// Forcer les notes entre 0 et 20
watch(matieres, (newVal) => {
  newVal.forEach(m => {
    const np = parseFloat(m.notePrincipale)
    if (!isNaN(np)) {
      if (np < 0) m.notePrincipale = '0'
      else if (np > 20) m.notePrincipale = '20'
    }
    const nc = parseFloat(m.noteControle)
    if (!isNaN(nc)) {
      if (nc < 0) m.noteControle = '0'
      else if (nc > 20) m.noteControle = '20'
    }
  })
}, { deep: true })

// Défilement
function scrollToControle() {
  if (sectionControle.value) {
    sectionControle.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}
function scrollToPrincipale() {
  if (sectionPrincipale.value) {
    sectionPrincipale.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}
watch(sessionActive, (val) => {
  nextTick(() => {
    if (val === 'controle') scrollToControle()
    else scrollToPrincipale()
  })
})

// Afficher résultat
function afficherResultat(type, moyenne, score, scorePlus7, passable) {
  resultData.value = { type, moyenne, score, scorePlus7, passable }
  showResultModal.value = true
}

function calculerPrincipale() {
  if (!allNotesPrincipaleFilled.value) {
    alert(props.t('fill_all_notes'))
    return
  }
  afficherResultat('principale', moyennePrincipale.value, scorePrincipale.value, scorePlus7Principale.value, passablePrincipale.value)
}

function calculerControle() {
  if (!allNotesPrincipaleFilled.value) {
    alert(props.t('principal_must_be_filled_first'))
    return
  }
  if (!controleValid.value) {
    alert(props.t('controle_fields_incomplete'))
    return
  }
  afficherResultat('controle', moyenneControle.value, scoreControle.value, scorePlus7Controle.value, passableControle.value)
}

async function enregistrerEtContinuer() {
  if (isSubmitting.value) return
  isSubmitting.value = true
  const moyenneData = {
    specialite: selectedSpecialite.value?.nom,
    matieres: matieres.value,
    moyenne: resultData.value.moyenne,
    score: resultData.value.score,
    score_plus_7: resultData.value.scorePlus7
  }
  const success = await moyennesStore.addMoyenne(moyenneData)
  isSubmitting.value = false
  if (success) {
    showResultModal.value = false
    emit('done')
  } else {
    alert(props.t('error_occurred') || 'Erreur lors de l\'enregistrement.')
  }
}
</script>

<template>
  <div>
    <!-- En-tête -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center">
        <button
          v-if="step === 'matieres'"
          @click="retourSpecialites"
          class="mr-4 p-2 rounded-full bg-white dark:bg-gray-800 shadow hover:shadow-md transition text-gray-600 dark:text-gray-300 hover:text-indigo-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </button>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
          {{ step === 'specialites' ? props.t('choose_speciality') : selectedSpecialite?.nom }}
        </h1>
      </div>
    </div>

    <!-- Spécialités -->
    <div v-if="step === 'specialites'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="spe in specialites"
        :key="spe.id"
        @click="selectSpecialite(spe)"
        class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all cursor-pointer overflow-hidden border border-gray-100 dark:border-gray-700 p-6"
      >
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br opacity-10 rounded-bl-full" :class="spe.color"></div>
        <div class="flex flex-col items-center">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br flex items-center justify-center text-white shadow-lg mb-4 transform group-hover:scale-110 transition-transform duration-300" :class="spe.color">
            <div v-html="spe.icon" class="w-8 h-8"></div>
          </div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white text-center">{{ spe.nom }}</h3>
          <p class="text-sm text-gray-500 mt-2">{{ props.t('click_to_enter_notes') }}</p>
        </div>
      </div>
    </div>

    <!-- Page de notes (deux sections) -->
    <div v-else>
      <!-- Switch principal/controle -->
      <div class="flex justify-center mb-6">
        <div class="flex bg-gray-200 dark:bg-gray-700 rounded-full p-1 shadow-inner">
          <button
            @click="sessionActive = 'principale'"
            :class="[
              'px-5 py-2 rounded-full text-sm font-medium transition-all duration-300',
              sessionActive === 'principale'
                ? 'bg-white dark:bg-gray-600 text-indigo-700 dark:text-indigo-300 shadow'
                : 'text-gray-600 dark:text-gray-400'
            ]"
          >{{ props.t('principal_session') }}</button>
          <button
            @click="sessionActive = 'controle'"
            :class="[
              'px-5 py-2 rounded-full text-sm font-medium transition-all duration-300',
              sessionActive === 'controle'
                ? 'bg-white dark:bg-gray-600 text-indigo-700 dark:text-indigo-300 shadow'
                : 'text-gray-600 dark:text-gray-400'
            ]"
          >{{ props.t('control_session') }}</button>
        </div>
      </div>

      <!-- SECTION PRINCIPALE -->
      <div ref="sectionPrincipale" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 md:p-8 mb-8">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">{{ props.t('principal_session') }}</h2>

        <!-- Dispense sport -->
        <div v-if="matieres.some(m => m.nom === 'Sport')" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
          <label class="flex items-center space-x-3 cursor-pointer">
            <input
              type="checkbox"
              v-model="showDispenseSport"
              @change="toggleDispenseSport"
              class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500"
            />
            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ props.t('sport_dispense_label') }}</span>
          </label>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-8">{{ props.t('sport_dispense_help') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-8">
          <div v-for="(m, idx) in matieres" :key="'p-'+idx"
               class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-transparent hover:border-indigo-200 dark:hover:border-indigo-600 transition">
            <div class="flex items-center space-x-2 flex-1 min-w-0">
              <span class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ m.nom }}</span>
              <span v-if="m.isMain" class="text-amber-500 dark:text-amber-400 text-lg leading-none" title="Matière principale">★</span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">×{{ m.coefficient }}</span>
            </div>
            <div class="flex items-center space-x-1 ml-2">
              <input
                v-if="!(m.hasDispense && m.nom === 'Sport')"
                v-model="m.notePrincipale"
                type="number" min="0" max="20" step="0.25"
                class="w-20 px-2 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm text-center"
                placeholder="0"
              />
              <span class="text-xs text-gray-500 dark:text-gray-400">/20</span>
              <span v-if="m.hasDispense && m.nom === 'Sport'" class="text-sm text-gray-500 dark:text-gray-400 italic">{{ props.t('dispensed') }}</span>
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <button @click="calculerPrincipale" :disabled="!allNotesPrincipaleFilled"
                  class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl hover:from-indigo-700 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition font-semibold">
            {{ props.t('calculate_principal') }}
          </button>
        </div>
      </div>

      <!-- SECTION CONTRÔLE -->
      <div ref="sectionControle" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 md:p-8">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"> {{ props.t('control_session') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          {{ props.t('control_info_text') }}
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-8">
          <div v-for="(m, idx) in matieres" :key="'c-'+idx"
               class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-transparent hover:border-indigo-200 dark:hover:border-indigo-600 transition">
            <div class="flex items-center space-x-2 flex-1 min-w-0">
              <input
                type="checkbox"
                v-model="m.repasser"
                :disabled="m.nom === 'Sport' && m.hasDispense"
                class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 disabled:opacity-40"
              />
              <span class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ m.nom }}</span>
              <span class="text-xs text-gray-400">
                ({{ m.notePrincipale || '?' }})
              </span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">×{{ m.coefficient }}</span>
            </div>
            <div class="flex items-center space-x-1 ml-2">
              <input
                v-if="m.repasser"
                v-model="m.noteControle"
                type="number" min="0" max="20" step="0.25"
                class="w-20 px-2 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition text-sm text-center"
                placeholder="0"
              />
              <span v-if="m.repasser" class="text-xs text-gray-500 dark:text-gray-400">/20</span>
              <span v-else class="text-sm text-gray-500 dark:text-gray-400">---</span>
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <button @click="calculerControle" :disabled="!controleValid"
                  class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl hover:from-indigo-700 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition font-semibold">
            {{ props.t('calculate_control') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal résultat -->
    <Teleport to="body">
      <div v-if="showResultModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8">
          <div class="text-center">
            <div v-if="resultData.passable" class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center">
              <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div v-else class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-red-400 to-pink-500 flex items-center justify-center">
              <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
              {{ resultData.type === 'principale' ? props.t('result_principal') : props.t('result_control') }}
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-4">
              {{ props.t('average') }} : <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ resultData.moyenne }}/20</span><br/>
              {{ props.t('score_orientation') }} : {{ resultData.score }}<br/>
              {{ props.t('score_plus_7') }} : {{ resultData.scorePlus7 }}<br/>
              <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm font-semibold
                bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                {{ mentionResult }}
              </span><br/>
              <span :class="resultData.passable ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="font-bold text-lg">
                {{ resultData.passable ? props.t('admis') : props.t('non_admis') }}
              </span>
            </p>
            <div class="flex flex-col gap-3">
              <button @click="enregistrerEtContinuer" class="w-full py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-medium flex items-center justify-center" :disabled="isSubmitting">
                <span v-if="!isSubmitting">{{ props.t('save_and_continue') }}</span>
                <span v-else>{{ props.t('saving') }}</span>
              </button>
              <button @click="showResultModal = false" class="w-full py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition font-medium">
                {{ props.t('close') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>