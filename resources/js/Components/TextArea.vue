<script setup lang="ts">
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: {
        type: String,
        required: true,
    },
    rows: {
        type: [String, Number],
        default: 4,
    },
});

defineEmits(['update:modelValue']);

const textarea = ref<HTMLTextAreaElement | null>(null);

onMounted(() => {
    if (textarea.value && textarea.value.hasAttribute('autofocus')) {
        textarea.value.focus();
    }
});

defineExpose({ focus: () => textarea.value?.focus() });
</script>

<template>
    <textarea
        ref="textarea"
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        :value="modelValue"
        :rows="rows"
        @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    ></textarea>
</template>
