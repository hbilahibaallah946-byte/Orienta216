<template>
  <div>
    <h1>Remplissez vos notes pour {{ section }}</h1>
    
    <div v-for="subject in subjects" :key="subject">
      <label :for="subject">{{ subject }}</label>
      <input type="number" v-model.number="marks[subject]" :id="subject" min="0" max="20"/>
    </div>

    <label>Session</label>
    <select v-model="session">
      <option value="principale">Principale</option>
      <option value="controle">Contrôle</option>
    </select>

    <button @click="submitForm">Calculer</button>
  </div>
</template>

<script>
export default {
  props: ['section'],
  data() {
    return {
      session: 'principale',
      marks: {},
      subjects: []
    }
  },
  created() {
    this.setSubjects();
  },
  methods: {
    setSubjects() {
      const map = {
        math: ['math', 'physics', 'info', 'arabic', 'french', 'english', 'philosophy', 'option', 'sport'],
        sc: ['math', 'physics', 'science', 'arabic', 'french', 'english', 'philosophy', 'info', 'option', 'sport'],
        lettres: ['arabic','french','english','philosophy','option','sport'],
        sport: ['sport','math','physics','info'],
        info: ['info','math','physics','arabic','french','english','option','sport'],
        eco: ['math','arabic','french','english','eco','option','sport']
      }
      this.subjects = map[this.section] || [];
      this.subjects.forEach(s => this.marks[s] = 0);
    },
    submitForm() {
      this.$inertia.post('/api/records', {
        ...this.marks,
        section: this.section,
        session: this.session
      }).then(response => {
        alert(`Moyenne: ${response.data.average}, Score: ${response.data.score}, Score+7%: ${response.data.score_boosted}`);
      })
    }
  }
}
</script>
