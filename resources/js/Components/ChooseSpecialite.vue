<template>
<div class="container">
    <h2>Choisir votre spécialité</h2>

    <select v-model="specialite">
        <option value="1">Math</option>
        <option value="2">Sciences</option>
        <option value="3">Lettres</option>
        <option value="4">Sport</option>
        <option value="5">Info</option>
        <option value="6">Technique</option>
        <option value="7">Economie et gestion</option>
    </select>

    <button @click="save">Continuer</button>
</div>
</template>

<script>
import axios from 'axios'

export default {
    data(){
        return{
            specialite:null
        }
    },
    methods:{
        save(){
            axios.post('/api/save-specialite',{
                specialite_id:this.specialite
            }).then(()=>{
                this.$router.push('/notes')
            })
        }
    }
}
</script>
