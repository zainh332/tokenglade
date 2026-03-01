<template>
    <div class="flex justify-between gap-4 py-2 px-2 border-b border-slate-100">
        <span class="text-slate-500 shrink-0">
            {{ label }}
        </span>
        <span class="text-slate-900 font-medium text-right break-all min-w-0">
            {{ value }}
        </span>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    value: [String, Number]
})
</script>