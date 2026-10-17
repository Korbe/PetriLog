<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="block text-sm font-medium text-(--app-ink)">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
