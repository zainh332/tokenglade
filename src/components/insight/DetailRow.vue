<template>
    <div class="flex justify-between border-b border-gray-100 pb-2">
        <span class="text-gray-500">{{ label }}</span>
        <span class="font-medium text-gray-900">{{ value }}</span>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    value: [String, Number]
})
</script>