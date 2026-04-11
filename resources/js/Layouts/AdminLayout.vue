<script setup>
import { router, Link, usePage } from '@inertiajs/vue3'

function logout(){
router.post('/logout')
}

const page = usePage()
</script>

<template>

<div class="flex min-h-screen bg-gray-100">

<!-- SIDEBAR -->

<div class="w-64 bg-white shadow-lg flex flex-col">

<div class="p-6 text-xl font-bold border-b">
Orienta
</div>

<nav class="flex flex-col p-4 gap-2">

<Link href="/admin/dashboard"
class="p-3 rounded-lg hover:bg-blue-100"
:class="page.url.startsWith('/admin/dashboard') ? 'bg-blue-500 text-white' : ''">
📊 Dashboard
</Link>

<Link href="/admin/filieres"
class="p-3 rounded-lg hover:bg-blue-100"
:class="page.url.startsWith('/admin/filieres') ? 'bg-blue-500 text-white' : ''">
🎓 Filières
</Link>

<Link href="/admin/etudiants"
class="p-3 rounded-lg hover:bg-blue-100"
:class="page.url.startsWith('/admin/etudiants') ? 'bg-blue-500 text-white' : ''">
👨‍🎓 Étudiants
</Link>

<Link
href="/admin/evaluation"
class="flex items-center p-3 rounded hover:bg-blue-100"
>
📝 Evaluation
</Link>


</nav>

<button
@click="logout"
class="bg-red-500 text-white m-4 py-2 rounded-lg mt-auto">
Déconnexion
</button>

</div>

<!-- CONTENT -->

<div class="flex-1 flex flex-col">

<!-- TOPBAR -->

<div class="bg-white shadow p-4 flex justify-between items-center">

<h2 class="font-semibold">
Admin Dashboard
</h2>

<div class="flex gap-4 items-center">

🔔

<img
src="https://i.pravatar.cc/40"
class="rounded-full"
/>

</div>

</div>

<div class="p-10">

<slot />

</div>

</div>

</div>

</template>
