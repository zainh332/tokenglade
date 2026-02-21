<template>
    <!-- <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
        <p class="text-xs text-gray-500">{{ title }}</p>
        <p class="text-lg font-semibold text-gray-900 mt-1">{{ value }}</p>
    </div> -->
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
        <p class="text-xs text-slate-500 font-medium">{{ title }}</p>
        <p class="text-2xl font-bold text-slate-900 mt-1">{{ value }}</p>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: [String, Number]
})
</script>