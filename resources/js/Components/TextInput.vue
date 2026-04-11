<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    id: String,
    name: String,
    type: {
        type: String,
        default: 'text'
    }
})

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        :id="id"
        :name="name"
        :type="type"
        v-bind="$attrs"
        v-model="model"
        ref="input"
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full"
    />
</template>
