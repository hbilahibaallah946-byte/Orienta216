<template>
<div>
    <h2>Saisir vos notes</h2>

    <input v-model="form.math" placeholder="Math">
    <input v-model="form.physique" placeholder="Physique">
    <input v-model="form.science" placeholder="Science">
    <input v-model="form.arabe" placeholder="Arabe">
    <input v-model="form.francais" placeholder="Français">
    <input v-model="form.anglais" placeholder="Anglais">
    <input v-model="form.philosophie" placeholder="Philosophie">
    <input v-model="form.informatique" placeholder="Informatique">
    <input v-model="form.option" placeholder="Option">
    <input v-model="form.sport" placeholder="Sport">

    <button @click="save">Calculer</button>

    <h3 v-if="moyenne">Votre moyenne : {{ moyenne }}</h3>
</div>
</template>

<script>
import axios from 'axios'

export default {
    data(){
        return{
            moyenne:null,
            form:{
                math:null,
                physique:null,
                science:null,
                arabe:null,
                francais:null,
                anglais:null,
                philosophie:null,
                informatique:null,
                option:null,
                sport:null
            }
        }
    },
    methods:{
        save(){
            axios.post('/api/save-notes',this.form)
            .then(res=>{
                this.moyenne=res.data.moyenne
            })
        }
    }
}
</script>
