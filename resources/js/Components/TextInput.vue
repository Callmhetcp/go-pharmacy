<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
});
</script>

<template>
    <input
        :value="modelValue"
        class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-base text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-500/10 disabled:cursor-not-allowed disabled:bg-slate-100"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>