<template>
    <div :class="['rounded-xl border p-5', styleClass]">
        <p class="text-sm text-gray-600">{{ title }}</p>
        <p class="text-2xl font-semibold mt-2 text-gray-900">
            {{ value }}
        </p>
    </div>
</template>

<script setup>
import { computed } from "vue"

const props = defineProps({
    title: String,
    value: String,
    type: String
})

const styleClass = computed(() => {
    if (props.type === "green") return "bg-green-50 border-green-100"
    if (props.type === "yellow") return "bg-yellow-50 border-yellow-100"
    return "bg-red-50 border-red-100"
})
</script>