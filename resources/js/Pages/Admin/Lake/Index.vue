<template>
    <WaterAdminIndex type="lake" :items="lakes" :states="states" />
</template>

<script setup>
import WaterAdminIndex from '../partials/WaterAdminIndex.vue'

defineProps({
    lakes: { type: Array, default: () => [] },
    states: { type: Array, default: () => [] },
})
</script>
